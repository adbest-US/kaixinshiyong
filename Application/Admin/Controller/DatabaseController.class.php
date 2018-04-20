<?php
/**
 * @version        $Id$
 * @author         jason
 * @copyright      Copyright (c) 2007 - 2014, Adalways Technology Co., Ltd.
 * @link           http://www.dealswill.com
**/
namespace Admin\Controller;
class DatabaseController extends \Admin\Controller\InitController
{
    public function _initialize() {
        parent::_initialize();
        $this->backup_path = RUNTIME_PATH.C('BACKUP_PATH');
        if(!file_exists($this->backup_path)) dmkdir($this->backup_path);
    }
    /**
     * 优化表
     * @param  String $tables 表名
     * @author 
     */
    public function optimize($tables = null){
        if($tables) {
            $Db   = M();
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = $Db->query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                    $this->success('数据表优化完成！');
                } else {
                    $this->error('数据表优化出错请重试！');
                }
            } else {
                $list = $Db->query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                	$this->success("数据表'{$tables}'优化完成！");
                } else {
                	$this->error("数据表'{$tables}'优化出错请重试！");
                }
            }
        } else {
            $this->error('请指定要优化的表！');
        }
    }

    /* 表结构 */
    public function showcreat($table = NULL) {
    	if($table) {
			$r = M()->query("SHOW CREATE TABLE ".$table);
			$structure = $r[0]['Create Table'];
			$show_header = false;
			include $this->admin_tpl('database_structure');	
    	} else {
    		$this->error('请指定数据表');
    	}
    }
    
    /* 删除备份文件 */
    public function delete() {
        if(submitcheck('dosubmit', 'GP')) {
            $filenames = $_POST['filenames'];
            if(empty($filenames)) {
                $this->error('请选择要删除的备份文件');
            }
            foreach ($filenames as $filename) {
                @unlink($this->backup_path.DIRECTORY_SEPARATOR.$filename);
            }
            $this->success('指定备份文件删除成功');
        } else {
            $this->error('请勿非法访问');
        }
    }

    /**
     * 修复表
     * @param  String $tables 表名
     * @author 
     */
    public function repair($tables = null){
        if($tables) {
            $Db   = M();
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = $Db->query("REPAIR TABLE `{$tables}`");
                if($list){
                	$this->success('数据表修复完成！');
                } else {
                    $this->error('数据表修复出错请重试！');
                }
            } else {
                $list = $Db->query("REPAIR TABLE `{$tables}`");
                if($list){
                	$this->success("数据表'{$tables}'修复完成！");
                } else {
                    $this->error("数据表'{$tables}'修复出错请重试！");
                }
            }
        } else {
            $this->error("请指定要修复的表！");
        }
    }

    /* 备份数据 */
    public function export() {
    	if (submitcheck('dosubmit', 'GP')) {
    		$param = I('param.');
    		$param['id'] = (int) $param['id'];
    		$param['start'] = (int) $param['start'];
    		$this->public_export($param['tables'], $param['id'], $param['start'], $param['sizelimit'], $param['compress']);
    	} else {
            $Db = M();
            $tables = $Db->query('SHOW TABLE STATUS');
			$lists = array();
			foreach($tables as $k => $table) {
				$name = $table['Name'];
				$row = array('name'=>$name,'rows'=>$table['Rows'],'size'=>$table['Data_length']+$row['Index_length'],'engine'=>$table['Engine'],'data_free'=>$table['Data_free'],'collation'=>$table['Collation'], 'comment' => $table['Comment']);
				$lists[$k] = $row;
			}
			include $this->admin_tpl('database_export');
    	}
    }

    /**
     * 备份数据库
     * @param  String  $tables 表名
     * @param  Integer $id     表ID
     * @param  Integer $start  起始行数
     * @author 
     */
    /**
     * 备份数据库
     * @param string    $tables     备份目标数据表
     * @param integer   $id         表ID
     * @param integer   $start      起始行数
     * @param integer   $part       分卷大小（KB）
     * @param integer   $compress   是否压缩
     * @param integer   $level      压缩级别
     * 
     */
    private function public_export($tables = null, $id = null, $start = null, $part = 500, $compress = 1, $level = 9){
        $param = array('dosubmit' => 1);
        if(IS_POST && !empty($tables) && is_array($tables)){ //初始化
            //读取备份配置
            $config = array(
                'path'     => $this->backup_path. DIRECTORY_SEPARATOR,
                'part'     => $part * 1000,
                'compress' => $compress,
                'level'    => $level,
            );

            //检查是否有正在执行的任务
            $lock = "{$config['path']}backup.lock";
            if(is_file($lock)){
            	$this->error('检测到有一个备份任务正在执行，请稍后再试！');
            } else {
                //创建锁文件
                file_put_contents($lock, NOW_TIME);
            }
            // 自动创建备份文件夹
            if(!file_exists($config['path']) || !is_dir($config['path'])) dmkdir($config['path']);
            //检查备份目录是否可写
            is_writeable($config['path']) || showmessage('备份目录不存在或不可写，请检查后重试！');
            session('backup_config', $config);
            //生成备份文件信息
            $file = array(
                'name' => date('Ymd-His', NOW_TIME),
                'part' => 1,
            );
            session('backup_file', $file);

            //缓存要备份的表
            session('backup_tables', $tables);

            //创建备份文件
            
            $Database = new \Common\Library\database($file, $config);
            if(false !== $Database->create()) {
            	$param['id'] = 0;
            	$param['start'] = 0;
            	$this->success('初始化成功', U('export', $param));
            } else {
            	$this->error('初始化失败，备份文件创建失败！');
            }
        } elseif (IS_GET && is_numeric($id) && is_numeric($start)) { //备份数据
            $tables = session('backup_tables');      
            //备份指定表
            $Database = new \Common\Library\database(session('backup_file'), session('backup_config'));
            $start  = $Database->backup($tables[$id], $start);
            if(false === $start){
            	$this->error('备份出错！');
            } elseif (0 === $start) { //下一表
                if(isset($tables[++$id])){
                    $param['id'] = $id;
                    $param['start'] = 0;
                    $this->error('数据库表 '.$tables[$id].' 备份完成...', U('export', $param), 1);
                } else { //备份完成，清空缓存
                    unlink($this->backup_path.DIRECTORY_SEPARATOR.'backup.lock');
                    session('backup_tables', null);
                    session('backup_file', null);
                    session('backup_config', null);
                    $this->success('备份成功', U('export'));
                }
            } else {
            	$param['id'] = $id;
            	$param['start'] = $start[0];
            	$rate = floor(100 * ($start[0] / $start[1]));
            	$this->success('正在备份'.$tables[$id].'...('.$rate.'%)', U('export', $param), 1);
            }
        } else {
            $this->error('参数错误！');
        }
    }

    public function import() {
    	if(submitcheck('dosubmit', 'GP')) {
    		$param = I('param.');
    		$this->public_import($param['time'], $param['part'], $param['start']);
    	} else {
	        //列出备份文件列表
	        $path = realpath($this->backup_path);
	        $flag = \FilesystemIterator::KEY_AS_FILENAME;
	        $glob = new \FilesystemIterator($path, $flag);
	        $list = array();
	        foreach ($glob as $name => $file) {
	            if (preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
	                $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

	                $date = "{$name[0]}-{$name[1]}-{$name[2]}";
	                $time = "{$name[3]}:{$name[4]}:{$name[5]}";
	                $part = $name[6];

	                if (isset($list["{$date} {$time}"])) {
	                    $info = $list["{$date} {$time}"];
	                    $info['part'] = max($info['part'], $part);
	                    $info['filesize'] = $info['size'] + $file->getSize();
	                } else {
	                    $info['part'] = $part;
	                    $info['filesize'] = $file->getSize();
	                }
	                $info['filename'] = basename($file);
	                $extension = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
	                $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
	                $info['time'] = strtotime("{$date} {$time}");
	                $list["{$date} {$time}"] = $info;
	            }
	        }
	        include $this->admin_tpl('database_import');
    	}
    }

    /**
     * 还原数据库
     * @author 
     */
    public function public_import($time = 0, $part = null, $start = null) {
    	$param = array('dosubmit' => 1);
        if(is_numeric($time) && is_null($part) && is_null($start)){ //初始化
            //获取备份文件信息
            $name  = date('Ymd-His', $time) . '-*.sql*';
            $path  = realpath($this->backup_path) . DIRECTORY_SEPARATOR . $name;
            $files = glob($path);
            $list  = array();
            foreach($files as $name){
                $basename = basename($name);
                $match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                $gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
                $list[$match[6]] = array($match[6], $name, $gz);
            }
            ksort($list);
            //检测文件正确性
            $last = end($list);
            if(count($list) === $last[0]){
                session('backup_list', $list); 
                $param['part'] = 1;
                $param['start'] = 0;
                $this->success('初始化完成！', U('import', $param));
            } else {
                $this->error('备份文件可能已经损坏，请检查！', U('import'));
            }
        } elseif(is_numeric($part) && is_numeric($start)) {
            $list  = session('backup_list');
            $db = new \Common\Library\database($list[$part], array(
                'path'     => realpath(C('BACKUP_PATH')) . DIRECTORY_SEPARATOR,
                'compress' => $list[$part][2]));

            $start = $db->import($start);
            if(false === $start){
                $this->error('还原数据出错！', U('import'));
            } elseif(0 === $start) { //下一卷
                if(isset($list[++$part])){
                	$param['part'] = $part;
                	$param['start'] = 0;
                	$this->success("正在还原...#{$part}", U('import', $param, 1));
                } else {
                	session('backup_list', null);
                	$this->success('还原完成！', U('import'));
                }
            } else {
                $param['part'] = $part;
                $param['start'] = $start[0];
                if($start[1]){
                    $rate = floor(100 * ($start[0] / $start[1]));
                    $this->success("正在还原...#{$part} ({$rate}%)", U('import', $param, 1));
                } else {
                	$param['gz'] = 1;
                	$this->success("正在还原...#{$part}", U('import', $param, 1));
                }
            }

        } else {
            $this->error('参数错误！');
        }
    }
}