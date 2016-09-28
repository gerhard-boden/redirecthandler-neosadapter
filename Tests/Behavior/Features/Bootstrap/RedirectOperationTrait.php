<?php
namespace Neos\RedirectHandler\NeosAdapter\Tests\Behavior\Features\Bootstrap;

/*
 * This file is part of the Neos.RedirectHandler.NeosAdapter package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use PHPUnit_Framework_Assert as Assert;
use TYPO3\Flow\Http\Request;
use Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository;
use Neos\RedirectHandler\NeosAdapter\Service\NodeRedirectService;
use Neos\RedirectHandler\DatabaseStorage\RedirectStorage;

/**
 * ToDo:.
 *
 * [x] Find a way to solve the issue that sourceURis look like `localPathToBehat/en/actualUri/`
 * [x] Retrieve persisted redirect within the same request (var_dump was empty because it's a Generator)
 * [x] Find out why i can't change a property without a `The target URI path of the node could not be resolved` exception
 * [x] Find out why a redirect in the german content dimension hast /en/ as source
 * [x] Find out why hidden nodes are not retrieved
 * [x] Fix content dimension fallback bug
 * [x] Write test scenarios that cover this bug
 * [x] Write more tests
 * [X] Make sure to note that this tests rely on changes done in Packages/Application/Neos.RedirectHandler.NeosAdapter/Classes/Service/NodeRedirectService.php
 */
trait RedirectOperationTrait
{
    /**
     * @BeforeScenario @fixtures
     */
    public function beforeRedirectScenarioDispatcher()
    {
        $this->resetRedirectInstances();
    }

    /**
     * @Given /^I have the following redirects:$/
     * @When /^I create the following redirects:$/
     */
    public function iHaveTheFollowingRedirects($table)
    {
        $rows = $table->getHash();
        $nodeRedirectStorage = $this->objectManager->get(RedirectStorage::class);
        $redirectRepository = $this->objectManager->get(RedirectRepository::class);

        foreach ($rows as $row) {
            $nodeRedirectStorage->addRedirect(
                $this->buildActualUriPath($row['sourceuripath']),
                $this->buildActualUriPath($row['targeturipath'])
            );
        }

        $redirectRepository->persistEntities();
    }

    /**
     * @Then /^A redirect should be created for the node with path "([^"]*)" and with the following context:$/
     */
    public function aRedirectShouldBeCreatedForTheNodeWithPathAndWithTheFollowingContext($path, $table)
    {
        $rows = $table->getHash();
        $context = $this->getContextForProperties($rows[0]);
        $workspace = $context->getWorkspace();
        $redirectNode = $context->getNode($path);
        $redirectService = $this->objectManager->get(NodeRedirectService::class);

        $redirectService->createRedirectsForPublishedNode($redirectNode, $workspace);
    }

    /**
     *  @Given /^I should have a redirect with sourceUri "([^"]*)" and targetUri "([^"]*)"$/
     */
    public function iShouldHaveARedirectWithSourceUriAndTargetUri($sourceUri, $targetUri)
    {
        $nodeRedirectStorage = $this->objectManager->get(RedirectStorage::class);
        $targetUri = $this->buildActualUriPath($targetUri);
        $sourceUri = $this->buildActualUriPath($sourceUri);

        $redirect = $nodeRedirectStorage->getOneBySourceUriPathAndHost($sourceUri);

        if ($redirect !== null) {
            Assert::assertEquals($targetUri, $redirect->getTargetUriPath(),
                'A redirect was created, but the target URI does not match'
            );
        } else {
            Assert::assertNotNull($redirect, 'No redirect was created for asserted sourceUri');
        }
    }

    /**
     *  @Given /^I should have no redirect with sourceUri "([^"]*)" and targetUri "([^"]*)"$/
     */
    public function iShouldHaveNoRedirectWithSourceUriAndTargetUri($sourceUri, $targetUri)
    {
        $nodeRedirectStorage = $this->objectManager->get(RedirectStorage::class);
        $targetUri = $this->buildActualUriPath($targetUri);
        $sourceUri = $this->buildActualUriPath($sourceUri);

        $redirect = $nodeRedirectStorage->getOneBySourceUriPathAndHost($sourceUri);

        if ($redirect !== null) {
            Assert::assertNotEquals($targetUri, $redirect->getTargetUriPath(),
                'An untwanted redirect was created for given source and target URI'
            );
        }

        Assert::assertNull($redirect);
    }

    /**
     *  @Given /^I should have no redirect with sourceUri "([^"]*)"$/
     */
    public function iShouldHaveNoRedirectWithSourceUri($sourceUri)
    {
        $nodeRedirectStorage = $this->objectManager->get(RedirectStorage::class);
        $sourceUri = $this->buildActualUriPath($sourceUri);

        $redirect = $nodeRedirectStorage->getOneBySourceUriPathAndHost($sourceUri);

        Assert::assertNull($redirect);
    }

    /**
     * Return the actual URI path since the request comes from CLI.
     *
     * @param $uri
     *
     * @return string
     */
    protected function buildActualUriPath($uri)
    {
        $httpRequest = Request::createFromEnvironment();

        return $httpRequest->getBaseUri()->getPath().'index.php/'.$uri;
    }

    /**
     * Makes sure to reset all redirect instances which might still be stored in the RedirectRepository.
     */
    public function resetRedirectInstances()
    {
        $this->objectManager->get(RedirectRepository::class)->removeAll();
    }
}
