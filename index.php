<?php
define('BASEPATH', '');
function scanFileNameRecursivly($path = '', &$name = array())
{
    $path  = $path == '' ? dirname(__FILE__) : $path;
    $lists = @scandir($path);

    if (! empty($lists))
    {
        foreach ($lists as $f)
        {
            if (is_dir($path . DIRECTORY_SEPARATOR . $f) && $f != ".." && $f != "." && $f != ".git" && $f != ".idea" && $f != ".svn" && $f != ".gitignore")
            {
                scanFileNameRecursivly($path . DIRECTORY_SEPARATOR . $f, &$name);
            }
            else if ($f != ".." && $f != "." && $f != ".git" && $f != ".idea" && $f != ".svn" && $f != ".gitignore")
            {
                $name[] = $path . DIRECTORY_SEPARATOR . $f;
            }
        }
    }
    return $name;
}

$source = "../English";
$target = "../Dutch";
$source_files = scanFileNameRecursivly($source);
$target_files = scanFileNameRecursivly($target);
if (isset($_REQUEST['Compare']))
{
    require_once($_REQUEST['source']);
    if (isset($lang))
    {
        $source_lang = $lang;
    }
    require_once($_REQUEST['target']);
    if (isset($lang))
    {
        $target_lang = $lang;
    }
}

if (isset($_REQUEST['Save']))
{
    $new_lang = '<?php
$lang = array(
';
    foreach ($_REQUEST as $variable => $value)
    {
        if (strpos($variable, "target_") === 0)
        {
            $var = str_replace("target_", "", $variable);
            $value = $_REQUEST[$variable];
            if (!isset($_REQUEST['target_html_' . $var]) || $_REQUEST['target_html_' . $var] != 'yes')
            {
                $value = htmlentities($value);
            }
            $new_lang .= sprintf("'%s' => '%s',\r\n", $var, addslashes($value));
        }
    }
    $new_lang .= "''=>''
    );";
    file_put_contents($_REQUEST['target'], $new_lang);
    echo "File updated!<br /><br />";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="nl">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>EE Language file translation tool</title>
</head>
<body>
    <form action="" method="post">
        Language file:<br />
        <select name="source">
            <?php foreach ($source_files as $source_file): ?>
            <option value="<?= htmlspecialchars($source_file); ?>"<?=isset($_REQUEST['source']) && $_REQUEST['source'] == $source_file ? ' selected="selected"' : '' ?>><?= htmlspecialchars($source_file); ?></option>
            <?php endforeach; ?>
        </select>
        <br/>
        Language file to update:<br />
        <select name="target">
            <?php foreach ($target_files as $target_file): ?>
            <option value="<?= htmlspecialchars($target_file); ?>"<?=isset($_REQUEST['target']) && $_REQUEST['target'] == $target_file ? ' selected="selected"' : '' ?>><?= htmlspecialchars($target_file); ?></option>
            <?php endforeach; ?>
        </select>
        <br />
        <input type="submit" name="Compare" value="Compare" title="Compare" />

        <?php if (isset($_REQUEST['Compare'])): ?>
        <table border="1">
            <tr>
                <th width="15%">Variable</th>
                <th width="5%">Is HTML?</th>
                <th width="40%">Source</th>
                <th width="40%">Target</th>
            </tr>
            <?php
            foreach ($source_lang as $variable => $value):
                if ($variable != ''):
                    ?>
                    <tr>
                        <td<?=!isset($target_lang[$variable]) || !isset($source_lang[$variable]) ? ' style="background-color: orange;"' : "" ?>><?= $variable; ?></td>
                        <td><? if (preg_match('%<([a-zA-Z0-9]*)[^>]*>.*</\1>%', $source_lang[$variable])) { echo 'Yes <input type="hidden" name="target_html_'. $variable . '" value="yes" />'; } else { echo "No"; } ?></td>
                        <td><?= isset($source_lang[$variable]) ? htmlspecialchars($source_lang[$variable]) : "<em>Removed</em>" ?></td>
                        <td><?= isset($source_lang[$variable]) ? '<textarea name="target_' . $variable . '" cols="120" rows="3">' . (isset($target_lang[$variable]) ? $target_lang[$variable] : '') . '</textarea>': "<em>Will be removed</em>"; ?></td>
                    </tr>
                    <?php
                endif;
            endforeach; ?>
        </table>
        <br />
        <input type="submit" name="Save" value="Save" title="Save" />
        <?php endif; ?>

    </form>
</body>
</html>