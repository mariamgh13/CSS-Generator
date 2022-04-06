<?php


$dir = $argv[$argc - 1];
// OPTION
$options = getopt("ri::s::", array(   
    "recursive",
    "output-image::",
    "output-style::",
));

$recursive = FALSE;
$outputImg = "sprite.png";
$outputCSS = "style.css";

foreach ($options as $option => $value) {
    if (($option == 'i' || $option == "output-image") && strlen($value) > 0) {
        $outputImg = $value;
    }
    if (($option == 's' || $option == "output-style") && strlen($value) > 0) {
        $outputCSS = $value;
    }
    if (($option == 'r' || $option == "recursive")) {
        $recursive = TRUE;
    }
}

function recursiveGetImages($path, $rec)
{
    $d = opendir($path);

    $images = [];

    while ($file = readdir($d)) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        if (is_dir($path . '/' . $file) && $rec == TRUE) {
            $images = $images + recursiveGetImages($path . '/' . $file, $rec);
        } else {
            if (preg_match('/.png/i', $file)) {
                $images[] = $path . '/' . $file;
            }
        }
    }
    closedir($d);

    return $images;
}


// VARIABLES DE LA NOUVELLE IMAGE

$newImgH = 0;
$newImgW = 0;

$images = recursiveGetImages($dir, $recursive);

foreach ($images as $file) {
    if (!is_dir($file) && $file != '.' && $file != '..' && substr($file, -4) === '.png') {
        $imgSize = getimagesize($file);
        $imgW = $imgSize[0];
        $imgH = $imgSize[1];

        // echo "size de la photo : " . $imgW . " x " . $imgH . "\n";

        $newImgH = $newImgH + $imgH;
        if ($imgW > $newImgW) {
            $newImgW = $imgW;
        }
    }
}

//echo "size de la photo finale : " . $newImgW . " x " . $newImgH . "\n";

$newImgRessource = imagecreatetruecolor($newImgW, $newImgH);

$posH = 0;

$cssFileNames = "";
$cssFiles = "";

foreach ($images as $file) {
    if (!is_dir($file) && $file != '.' && $file != '..' && substr($file, -4) === '.png') {
        $imgSize = getimagesize($file);
        $imgW = $imgSize[0];
        $imgH = $imgSize[1];

        //  RESSOURCES DES IMAGES
        $imgRessource = imagecreatefrompng($file);

        if (!imagecopy($newImgRessource, $imgRessource, 0, $posH, 0, 0, $imgW, $imgH)) {
            die('Erreur : impossible de copier ' . $$imgPath);
        }

        $formattedFileName = "" . str_replace(' ', '-', trim(preg_replace("/[^A-Za-z0-9]+/", ' ', substr($file, 0, strlen($file) - 4))));

        $cssFiles = $cssFiles . ".img-" . $formattedFileName . " { background-position: -0px -" . $posH . "px; width: " . $imgW . "px; height: " . $imgH . "px; }\n";

        if (strlen($cssFileNames) > 0) {
            $cssFileNames = $cssFileNames . ", .img-" . $formattedFileName;
        } else {
            $cssFileNames = $cssFileNames . ".img-" .  $formattedFileName;
        }


        $posH = $posH + $imgH;
    }
}

//CSS
imagepng($newImgRessource, $outputImg);
imagedestroy($newImgRessource);

$css = fopen($outputCSS, "w");

fwrite($css, $cssFileNames . "\n" . "{ display: inline-block; background: url('" . $outputImg . "') no-repeat; overflow: hidden; text-indent: -9999px; text-align: left; }\n" . $cssFiles);

fclose($css);