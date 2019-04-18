<?php

namespace Tests\PHPCensor\Model;

use PHPCensor\Exception\InvalidArgumentException;
use PHPCensor\Model\Project;
use PHPCensor\Model;

/**
 * Unit tests for the Project model class.
 *
 * @author Dan Cryer <dan@block8.co.uk>
 */
class ProjectTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute_TestIsAValidModel()
    {
        $project = new Project();
        self::assertTrue($project instanceof Model);

        try {
            $project->setArchived('true');
        } catch (InvalidArgumentException $e) {
            self::assertEquals(
                'Column "archived" must be a bool.',
                $e->getMessage()
            );
        }
    }

    public function testExecute_TestGitDefaultBranch()
    {
        $project = new Project();
        $project->setType('git');

        self::assertEquals('master', $project->getBranch());
    }

    public function testExecute_TestGithubDefaultBranch()
    {
        $project = new Project();
        $project->setType(Project::TYPE_GITHUB);

        self::assertEquals('master', $project->getBranch());
    }

    public function testExecute_TestGitlabDefaultBranch()
    {
        $project = new Project();
        $project->setType(Project::TYPE_GITLAB);

        self::assertEquals('master', $project->getBranch());
    }

    public function testExecute_TestBitbucketDefaultBranch()
    {
        $project = new Project();
        $project->setType(Project::TYPE_BITBUCKET);

        self::assertEquals('master', $project->getBranch());
    }

    public function testExecute_TestMercurialDefaultBranch()
    {
        $project = new Project();
        $project->setType(Project::TYPE_HG);

        self::assertEquals('default', $project->getBranch());
    }

    public function testExecute_TestProjectAccessInformation()
    {
        $info = [
            'item1' => 'Item One',
            'item2' => 2,
        ];

        $project = new Project();
        $project->setAccessInformation($info);

        self::assertEquals('Item One', $project->getAccessInformation('item1'));
        self::assertEquals(2, $project->getAccessInformation('item2'));
        self::assertNull($project->getAccessInformation('item3'));
        self::assertEquals($info, $project->getAccessInformation());
    }
}
