<?php


if (!isset($_SESSION)) {
    session_start();
}

define ('MAX_FILE_SIZE', 2048 * 1536);

require_once('../../path.php');



$auth_users = "1,20";
check_auth($auth_users, $site_root);


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    
}

require_once('../../path.php');


$root = $_SERVER['DOCUMENT_ROOT'];
$root = substr($root, 0, strlen($root) - 1);
if (!isset($_SESSION['cur_folder']))
    $_SESSION['cur_folder'] = dirname($_SERVER['SCRIPT_FILENAME']);

$folder = NULL;
if (isset($_GET['f']))
    $folder = $_GET['f'];

if (isset($folder)) {
    if ($folder == 'top') {
        $paths = explode('/', $_SESSION['cur_folder']);
        $pos = count($paths) - 1;
        $level = (isset($_GET['l'])) ? $_GET['l'] : 1;
        for ($i = 0; $i < $level; $i++) {
            unset($paths[$pos--]);
        }

        $_SESSION['cur_folder'] = implode('/', $paths);
    }
    else {
        $_SESSION['cur_folder'] .= "/{$folder}";
    }
    header('Location: index.php');
}

$msg = '';

$root_path = explode('/', $root);
$root_count = count($root_path);

$cur_path = explode('/', $_SESSION['cur_folder']);
$cur_count = count($cur_path);

if ($root_count < $cur_count) {
    $breadcrumb = '';
    for ($i = $root_count; $i < $cur_count; $i++) {
        $step = strtolower($cur_path[$i]);
        if ($i == $cur_count - 1) {
            $breadcrumb .= "/ {$step}";
        }
        else {
            $level = $cur_count - $i - 1;
            $breadcrumb .= "/ <a href='?f=top&l={$level}'>{$step}</a> ";
        }
    }
}
else {
    $breadcrumb = '/';
    $_SESSION['cur_folder'] = $root;
}

if (isset($_POST['upload'])) {
    $msg = uploadFile($_SESSION['cur_folder'] . '/', 'upload', MAX_FILE_SIZE);
}

$files = @scandir($_SESSION['cur_folder'], SORT_STRING);
$cur_dir = $_SESSION['cur_folder'];
$dir_files = array();

$excl = array('.', '..');

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}

?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>

            <div id="main">
              <div class="container-fluid nav-fixed">
                <?php include INCPATH."/page_header.php" ?>
<!--                                    <div class="breadcrumbs">
                                        <ul>
                                            <li>
                                                <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                                            </li>
                                            <li>
                                                <a href="college.php">College</a>
                                            </li>
                                        </ul>
                                        <div class="close-bread">
                                            <a href="#"><i class="icon-remove"></i></a>
                                        </div>
                                    </div>
                                    <br/>-->
                                   

                <div class="row-fluid">
                    <div class="span12">
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-upload"></i>
                                    Admin File Upload
                                </h3>
                            </div>
                            <div class="box-content">
                                <div class="row-fluid">
                                    <div class="span12">
                                        <table  class="table table-striped table-striped">
                                            <?php if (isset($msg) && $msg != '') { ?>
                                                <tr>
                                                    <td><?php echo $msg; ?></td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td><?php echo $breadcrumb; ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" class="form form-horizontal">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Select the file you wan to Upload </label>
                                                            <div class="controls">
                                                                <div data-provides="fileupload" class="fileupload fileupload-new"><input type="hidden">
                                                                    <div class="input-append">
                                                                        <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i> 
                                                                            <span class="fileupload-preview"></span>
                                                                        </div>
                                                                        <span class="btn btn-file">
                                                                            <span class="fileupload-new">Select file</span>
                                                                            <span class="fileupload-exists">Change</span>
                                                                            <input type="file" name="filename">
                                                                        </span>
                                                                        <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-actions">
                                                            <input type='submit'  class="btn btn-small btn-purple" name='upload' value='Upload'/>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <?php
                                            if ($files) {
                                                foreach ($files as $file) {
                                                    if (in_array($file, $excl) || substr($file, 0, 1) == '.') {
                                                        continue;
                                                    }

                                                    if (is_dir("{$cur_dir}/{$file}")) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo "<a href='?f={$file}'>{$file}</a>"; ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    else
                                                        array_push($dir_files, $file);
                                                    ?>

                                                <?php
                                                }
                                                foreach ($dir_files as $file) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $file; ?></td>
                                                    </tr>
    <?php }
}
else { ?>
                                                <tr>
                                                    <td>Could not display content of the specified folder!</td>
                                                </tr>
<?php } ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p>&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>          
    </div>
<?php include INCPATH."/footer.php" ?>
</body>
</html>