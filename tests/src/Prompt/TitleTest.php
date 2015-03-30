<?php

namespace CL\ComposerInit\Test\Prompt;

use PHPUnit_Framework_TestCase;
use CL\ComposerInit\Prompt\TitlePrompt;
use CL\ComposerInit\Prompt\GitConfig;
use CL\ComposerInit\Prompt\Inflector;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass CL\ComposerInit\Prompt\TitlePrompt
 */
class TitlePromptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getGithub
     * @covers ::getInflector
     */
    public function testConstruct()
    {
        $gitConfig = new GitConfig();
        $github = new GithubMock();
        $inflector = new Inflector();
        $prompt = new TitlePrompt($gitConfig, $github, $inflector);

        $this->assertSame($gitConfig, $prompt->getGitConfig());
        $this->assertSame($github, $prompt->getGithub());
        $this->assertSame($inflector, $prompt->getInflector());
    }

    /**
     * @covers getDefault
     */
    public function testGetDefaultNull()
    {
        $getConfig = $this
            ->getMockBuilder('CL\ComposerInit\Prompt\GitConfig')
            ->getMock();

        $getConfig
            ->method('getOrigin')
            ->willReturn(null);

        $inflector = $this
            ->getMockBuilder('CL\ComposerInit\Prompt\Inflector')
            ->getMock();

        $inflector
            ->method('title')
            ->with(getcwd())
            ->willReturn('INFLECTED');

        $github = new GithubMock();
        $prompt = new TitlePrompt($getConfig, $github, $inflector);

        $this->assertEquals('INFLECTED', $prompt->getDefault());
        $this->assertEmpty($github->getHistory());
    }

    /**
     * @covers getDefault
     */
    public function testGetDefaultGithub()
    {
        $getConfig = $this
            ->getMockBuilder('CL\ComposerInit\Prompt\GitConfig')
            ->getMock();

        $getConfig
            ->method('getOrigin')
            ->willReturn('octocat/Hello-World');

        $github = new GithubMock();
        $github->queueResponse('repo.json');

        $inflector = new Inflector();

        $prompt = new TitlePrompt($getConfig, $github, $inflector);

        $this->assertEquals(
            'Hello World',
            $prompt->getDefault()
        );
        $request = $github->getHistory()->getLastRequest();
        $this->assertEquals(
            '/repos/octocat/Hello-World',
            $request->getUrl()
        );
    }

    /**
     * @covers getValues
     */
    public function testGetValues()
    {
        $prompt = $this
            ->getMockBuilder('CL\ComposerInit\Prompt\TitlePrompt')
            ->disableOriginalConstructor()
            ->setMethods(['getDefault'])
            ->getMock();

        $prompt
            ->method('getDefault')
            ->willReturn('TITLE');

        $output = new NullOutput();

        $dialog = $this
            ->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
            ->getMock();

        $dialog
            ->method('ask')
            ->with(
                $this->identicalTo($output),
                '<info>Title</info> (TITLE): ',
                'TITLE'
            )
            ->willReturn('NEW_TITLE');

        $values = $prompt->getValues($output, $dialog);
        $expected = [
            'title' => 'NEW_TITLE',
            'title_underline' => '=========',
        ];

        $this->assertEquals($expected, $values);
    }
}
