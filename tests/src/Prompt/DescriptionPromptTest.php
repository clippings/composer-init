<?php

namespace CL\ComposerInit\Test\Prompt;

use PHPUnit_Framework_TestCase;
use CL\ComposerInit\Test\ClientMock;
use CL\ComposerInit\Prompt\DescriptionPrompt;
use CL\ComposerInit\GitConfig;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass CL\ComposerInit\Prompt\DescriptionPrompt
 */
class DescriptionPromptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getGitConfig
     * @covers ::getGithub
     */
    public function testConstruct()
    {
        $GitConfig = new GitConfig();
        $github = new ClientMock();
        $prompt = new DescriptionPrompt($GitConfig, $github);

        $this->assertSame($GitConfig, $prompt->getGitConfig());
        $this->assertSame($github, $prompt->getGithub());
    }

    /**
     * @covers ::getDefault
     */
    public function testGetDefaultNull()
    {
        $gitConfig = $this
            ->getMockBuilder('CL\ComposerInit\GitConfig')
            ->getMock();

        $gitConfig
            ->method('getOrigin')
            ->willReturn(null);

        $github = new ClientMock();
        $prompt = new DescriptionPrompt($gitConfig, $github);

        $this->assertNull($prompt->getDefault());
        $this->assertEmpty($github->getHistory());
    }

    /**
     * @covers ::getDefault
     */
    public function testGetDefaultGithub()
    {
        $gitConfig = $this
            ->getMockBuilder('CL\ComposerInit\GitConfig')
            ->getMock();

        $gitConfig
            ->method('getOrigin')
            ->willReturn('octocat/Hello-World');

        $github = new ClientMock();
        $github->queueResponse('github/repo.json');

        $prompt = new DescriptionPrompt($gitConfig, $github);

        $this->assertEquals(
            'This your first repo!',
            $prompt->getDefault()
        );
        $history = $github->getHistory();
        $this->assertEquals(
            '/repos/octocat/Hello-World',
            (string) $history[0]['request']->getUri()
        );
    }

    /**
     * @covers ::getValues
     */
    public function testGetValues()
    {
        $prompt = $this
            ->getMockBuilder('CL\ComposerInit\Prompt\DescriptionPrompt')
            ->disableOriginalConstructor()
            ->setMethods(['getDefault'])
            ->getMock();

        $prompt
            ->method('getDefault')
            ->willReturn('TEST_DESCRIPTION');

        $output = new NullOutput();

        $dialog = $this
            ->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')
            ->getMock();

        $dialog
            ->method('ask')
            ->with(
                $this->identicalTo($output),
                '<info>Description</info> (TEST_DESCRIPTION): ',
                'TEST_DESCRIPTION'
            )
            ->willReturn('NEW_DESCRIPTION');

        $values = $prompt->getValues($output, $dialog);
        $this->assertEquals(['description' => 'NEW_DESCRIPTION'], $values);
    }
}
