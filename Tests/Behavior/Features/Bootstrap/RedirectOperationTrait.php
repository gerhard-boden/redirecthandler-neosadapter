<?php
namespace Neos\RedirectHandler\NeosAdapter\Tests\Behavior\Features\Bootstrap;

/*
 * This file is part of the TYPO3.TYPO3CR package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * ToDo:
 *
 * [x] Find a way to solve the issue that sourceURis look like `localPathToBehat/en/actualUri/`
 * [x] Retrieve persisted redirect within the same request (var_dump was empty because it's a Generator)
 * [ ] Find out why i can't change a property without a `The target URI path of the node could not be resolved` exception
 * [ ] Write more tests
 *
 */

use Neos\RedirectHandler\DatabaseStorage\Domain\Repository\RedirectRepository;
use Neos\RedirectHandler\DatabaseStorage\RedirectStorage;
use PHPUnit_Framework_Assert as Assert;
use Neos\RedirectHandler\NeosAdapter\Service\NodeRedirectService;
use TYPO3\Flow\Http\Request;

trait RedirectOperationTrait
{
    /**
     * @Given /^I have the following redirects:$/
     * @When /^I create the following redirects:$/
     */
    public function iHaveTheFollowingRedirects($table)
    {
        $rows = $table->getHash();
        $nodeRedirectStorage= new RedirectStorage();

        foreach ($rows as $row) {
            $nodeRedirectStorage->addRedirect(
                $this->buildActualUriPath($row['sourceuripath']),
                $this->buildActualUriPath($row['targeturipath'])
            );
        }

        $redirectRepository = new RedirectRepository();
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
        $redirectService = new NodeRedirectService();

        $redirectService->createRedirectsForPublishedNode($redirectNode, $workspace);
    }

    /**
     *  @Given /^I should have a redirect with sourceUri "([^"]*)" and TargetUri "([^"]*)"$/
     *
     */
    public function aRedirectShouldBeCreatedWithSourceuriAndTargeturi($sourceUri, $targetUri)
    {
       $nodeRedirectStorage= new RedirectStorage();
//        foreach ($nodeRedirectStorage->getAll() as $redirect) {
//            var_dump ($redirect);
//        }
        $targetUri = $this->buildActualUriPath($targetUri);
        $sourceUri = $this->buildActualUriPath($sourceUri);

        $redirect = $nodeRedirectStorage->getOneBySourceUriPathAndHost($sourceUri);

        if ($redirect !== null) {
            Assert::assertEquals($targetUri, $redirect->getTargetUriPath());
        } else {
            Assert::assertEquals($targetUri, null);
        }
    }

    /**
     * Return the actual URI path since the request comes from CLI
     *
     * @param $uri
     * @return string
     */
    protected function buildActualUriPath($uri) {
        $httpRequest = Request::createFromEnvironment();
        return $httpRequest->getBaseUri()->getPath().'index.php/'.$uri;
    }
}
