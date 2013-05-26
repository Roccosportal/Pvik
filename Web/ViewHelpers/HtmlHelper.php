<?php

namespace Pvik\Web\ViewHelpers;

use Pvik\Core\Path;

/**
 * Contains useful functionc for a view.
 */
class HtmlHelper {

    /**
     * Creates a html link.
     * <a href="/blog/overview/">Title</a>
     * @param string $path Resolves the ~/ to a relative path
     * @param string $title
     * @param array $htmlAttributes 
     */
    public function link($path, $title, $htmlAttributes = array()) {
        $relativePath = Path::relativePath($path);
        $linkHtml = '<a';
        $htmlAttributes['href'] = $relativePath;
        $linkHtml .= $this->generateAttributes($htmlAttributes);
        $linkHtml .= '>' . $title . '</a>';
        echo $linkHtml;
    }

    /**
     * Same as echo.
     * @param string $html 
     */
    public function out($html) {
        echo $html;
    }

    /**
     * Converts an assosciative array to a html string.
     * array ("ID" => "myid", "class" = "myclass")
     * to
     * ID="myID" class="myclass"
     * @param array $htmlAttributes
     * @return string 
     */
    public function generateAttributes(array $htmlAttributes) {
        $html = '';
        foreach ($htmlAttributes as $name => $value) {
            $html .= ' ' . $name . '="' . $value . '"';
        }
        return $html;
    }

    /**
     * Creates a link to a stylesheet file.
     * Output example:
     * <link rel="stylesheet" type="text/css" href="/css/stylesheet.css" />
     * @param string $path Resolves the ~/ to a relative path
     */
    public function styleSheetLink($path) {
        $relativePath = Path::relativePath($path);
        $html = '<link rel="stylesheet" type="text/css" href="' . $relativePath . '" />';
        echo $html;
    }

    /**
     * Creates a link to a javascript file.
     * Output example:
     * <script type="text/javascript" src="/js/javascript.js"></script>
     * @param string $path Resolves the ~/ to a relative path
     */
    public function javaScriptLink($path) {
        $relativePath = Path::relativePath($path);
        $html = '<script type="text/javascript" src="' . $relativePath . '"></script>';
        echo $html;
    }

    /**
     * Creates a link to a fav icon.
     * Output example:
     * <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
     * <link rel="icon" href="/favicon.ico" type="image/x-icon" />
     * @param type $path 
     */
    public function faviconLink($path) {
        $relativePath = Path::relativePath($path);
        $html = '<link rel="shortcut icon" href="' . $relativePath . '" type="image/x-icon" />';
        $html .= '<link rel="icon" href="' . $relativePath . '" type="image/x-icon" />';
        echo $html;
    }

    /**
     * Creates a errofield if a error exists in the validation state for the field.
     * Output example:
     * <span class="errorfield">Field can not be empty.</span>
     * @param ValidationState $validationState
     * @param string $field
     * @param string $class Html class
     */
    public function errorfield(\Pvik\Utils\ValidationState $validationState, $field, $class = 'errorfield') {
        if ($validationState != null) {
            if ($validationState->getError($field) != null) {
                echo '<span class="' . $class . '">' . $validationState->getError($field) . '</span>';
            }
        }
    }

}
