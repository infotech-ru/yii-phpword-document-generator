<?php
/*
 * This file is part of the infotech/yii-phpword-document-generator package.
 *
 * (c) Infotech, Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infotech\PhpWordDocumentGenerator;

use Infotech\DocumentGenerator\Renderer\RendererInterface;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;

class PhpWordRenderer implements RendererInterface
{
    /**
     * Render template with data.
     *
     * @param string $templatePath
     * @param array $data
     * @return string Rendered document as binary string
     */
    public function render($templatePath, array $data)
    {
        $savedEscapingSetting = Settings::isOutputEscapingEnabled();
        Settings::setOutputEscapingEnabled(true);

        $doc = new TemplateProcessor($templatePath);
        foreach ($data as $placeholder => $value) {
            $doc->setValue($placeholder, $value);
        }

        $file = $doc->save();

        Settings::setOutputEscapingEnabled($savedEscapingSetting);

        return $this->getTemporaryFileContents($file);
    }

    /**
     * @param string $filePath
     * @return string
     */
    private function getTemporaryFileContents($filePath)
    {
        $contents = file_get_contents($filePath);
        unlink($filePath);
        return $contents;
    }
}
