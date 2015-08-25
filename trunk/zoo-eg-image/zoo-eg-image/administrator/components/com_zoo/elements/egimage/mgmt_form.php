
<?php
//EG. Форма добравления/удаления изображений из галереи.

$jsVar = "{
    uploadURL: '$uploadURL',
    deleteURL: '$deleteURL',
    reorderURL: '$reorderURL'
}";
$jsVarName = 'eg_jsVar_'.rand(99, 9999);

$output = '
    '.eg_loadScriptOnce('/eg_assets/js/lib/jquery-1.3.2.min.js', true).'
    '.eg_loadScriptOnce('/eg_assets/js/lib/aim_0.1.js', true).'
    '.eg_loadScriptOnce('/eg_assets/js/app/eg-image-zoo-1.1.js', true).'
    '.eg_loadStyleOnce('/eg_assets/css/eg-image-zoo-1.1.css', true).'
<script>
    '.$jsVarName.' = '.$jsVar.';
</script>

<table class="eg-image-container">
    <tr>
        <td class="eg-item-image-holder">';
if (!$noImage) {
    $output .= "<img src=\"".$imgLink."\" alt=\"image\"/>";
}else {
    $output .='<span style="color:red;">файл не выбран</span>';
}

$output .='
</td>
</tr>
<tr>
        <td>
            <button type="button"
onclick="eg.image.upload(this, '.$jsVarName.')"
 id="eg-flash-upload">Загрузить другой</button>
        </td>
    </tr>
</table>
';

?>

