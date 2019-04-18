<?php

namespace PHPCensor\Service;

use PHPCensor\Model\Build;
use PHPCensor\Model\Project;
use PHPCensor\Store\ProjectStore;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The project service handles the creation, modification and deletion of projects.
 */
class ProjectService
{
    /**
     * @var \PHPCensor\Store\ProjectStore
     */
    protected $projectStore;

    /**
     * @param ProjectStore $projectStore
     */
    public function __construct(ProjectStore $projectStore)
    {
        $this->projectStore = $projectStore;
    }

    /**
     * Create a new project model and use the project store to save it.
     *
     * @param string  $title
     * @param string  $type
     * @param string  $reference
     * @param int $userId
     * @param array   $options
     *
     * @return \PHPCensor\Model\Project
     */
    public function createProject($title, $type, $reference, $userId, $options = [])
    {
        // Create base project and use updateProject() to set its properties:
        $project = new Project();
        $project->setCreateDate(new \DateTime());
        $project->setUserId((int)$userId);

        return $this->updateProject($project, $title, $type, $reference, $options);
    }

    /**
     * Update the properties of a given project.
     *
     * @param Project $project
     * @param string $title
     * @param string $type
     * @param string $reference
     * @param array $options
     *
     * @return \PHPCensor\Model\Project
     */
    public function updateProject(Project $project, $title, $type, $reference, $options = [])
    {
        // Set basic properties:
        $project->setTitle($title);
        $project->setType($type);
        $project->setReference($reference);
        $project->setAllowPublicStatus(false);
        $project->setDefaultBranchOnly(false);
        $project->setOverwriteBuildConfig(true);

        // Handle extra project options:
        if (array_key_exists('ssh_private_key', $options)) {
            $project->setSshPrivateKey($options['ssh_private_key']);
        }

        if (array_key_exists('ssh_public_key', $options)) {
            $project->setSshPublicKey($options['ssh_public_key']);
        }

        if (array_key_exists('overwrite_build_config', $options)) {
            $project->setOverwriteBuildConfig($options['overwrite_build_config']);
        }

        if (array_key_exists('build_config', $options)) {
            $project->setBuildConfig($options['build_config']);
        }

        if (array_key_exists('allow_public_status', $options)) {
            $project->setAllowPublicStatus($options['allow_public_status']);
        }

        if (array_key_exists('archived', $options)) {
            $project->setArchived($options['archived']);
        }

        if (array_key_exists('branch', $options)) {
            $project->setBranch($options['branch']);
        }

        if (array_key_exists('default_branch_only', $options)) {
            $project->setDefaultBranchOnly($options['default_branch_only']);
        }

        if (array_key_exists('group', $options)) {
            $project->setGroupId($options['group']);
        }

        $project = $this->processAccessInformation($project);

        // Save and return the project:
        /** @var Project $project */
        $project = $this->projectStore->save($project);

        if (array_key_exists('environments', $options)) {
            $project->setEnvironments($options['environments']);
        }

        return $project;
    }

    /**
     * Delete a given project.
     *
     * @param Project $project
     *
     * @return bool
     */
    public function deleteProject(Project $project)
    {
        try {
            $fileSystem = new Filesystem();

            $fileSystem->remove(RUNTIME_DIR . 'builds/' . $project->getId());
            $fileSystem->remove(PUBLIC_DIR . 'artifacts/pdepend/' . $project->getId());
            $fileSystem->remove(PUBLIC_DIR . 'artifacts/phpunit/' . $project->getId());
        } catch (\Exception $e) {
        }

        return $this->projectStore->delete($project);
    }

    /**
     * In circumstances where it is necessary, populate access information based on other project properties.
     *
     * @param Project $project
     *
     * @return Project
     */
    protected function processAccessInformation(Project $project)
    {
        $reference = $project->getReference();

        if (in_array($project->getType(), [
            Project::TYPE_GITHUB,
            Project::TYPE_GITLAB
        ], true)) {
            $info = [];

            if (preg_match(
                '#^((https|http|ssh)://)?((.+)@)?(([^/:]+):?)(:?([0-9]*)/?)(.+)\.git#',
                $reference,
                $matches
            )) {
                if (isset($matches[4]) && $matches[4]) {
                    $info['user'] = $matches[4];
                }

                if (isset($matches[6]) && $matches[6]) {
                    $info['domain'] = $matches[6];
                }

                if (isset($matches[8]) && $matches[8]) {
                    $info['port'] = $matches[8];
                }

                if (isset($matches[9]) && $matches[9]) {
                    $info['reference'] = $matches[9];

                    $project->setReference($matches[9]);
                }

                $info['origin'] = $reference;
            }

            $project->setAccessInformation($info);
        }

        return $project;
    }
}
