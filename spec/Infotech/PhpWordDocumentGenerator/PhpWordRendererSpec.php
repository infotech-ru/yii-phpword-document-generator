<?php

namespace spec\Infotech\PhpWordDocumentGenerator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PhpWordRendererSpec extends ObjectBehavior
{
    public function getMatchers()
    {
        $extractDocument = function ($contents) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'spec_fixture_');
            file_put_contents($tmpfile, $contents);
            $docContents = @file_get_contents('zip://' . $tmpfile . '#word/document.xml');
            unlink($tmpfile);
            return $docContents;
        };
        return [
            'beDocXDocument' => function ($subject) use ($extractDocument) {
                    libxml_use_internal_errors(TRUE);
                    $dom = new \DOMDocument();
                    $dom->loadXML($extractDocument($subject));
                    $parseErrors = libxml_get_errors();
                    return !$parseErrors;
            },
            'contains' => function($subject, $strings) use ($extractDocument) {
                    $docContents = $extractDocument($subject);
                    $strings = array_map(function ($s) { preg_quote(htmlentities($s)); }, (array)$strings);
                    preg_match_all('/' . implode('|', $strings) . '/', $docContents, $matches);
                    return !array_diff($strings, $matches[0]);
            }
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Infotech\PhpWordDocumentGenerator\PhpWordRenderer');
    }

    function it_should_fillup_template_with_values()
    {
        $fixtureTemplate = __DIR__ . '/../../fixtures/simple_template.docx';
        $data = [
            'PLACEHOLDER_1' => 'Replaced </b> placeholder 1',
            'PLACEHOLDER_2' => 'Replaced @ placeholder 2',
            'PLACEHOLDER_3' => 'Replaced & placeholder 3',
            'PLACEHOLDER_4' => 'Replaced % placeholder 4',
        ];

        $result = $this->render($fixtureTemplate, $data);
        $result->shouldBeDocXDocument();
        $result->shouldContains($data);
    }
}
