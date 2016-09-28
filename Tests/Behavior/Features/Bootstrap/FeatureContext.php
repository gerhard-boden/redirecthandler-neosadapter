<?php

require_once(__DIR__ . '/../../../../../Flowpack.Behat/Tests/Behat/FlowContext.php');
require_once(__DIR__ . '/../../../../../../Neos/TYPO3.TYPO3CR/Tests/Behavior/Features/Bootstrap/NodeOperationsTrait.php');
require_once(__DIR__ . '/RedirectOperationTrait.php');
require_once(__DIR__ . '/../../../../../../Framework/TYPO3.Flow/Tests/Behavior/Features/Bootstrap/IsolatedBehatStepsTrait.php');

use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Tests\Behavior\Features\Bootstrap\IsolatedBehatStepsTrait;
use TYPO3\TYPO3CR\Tests\Behavior\Features\Bootstrap\NodeOperationsTrait;
use TYPO3\Flow\Utility\Environment;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;
use Neos\RedirectHandler\NeosAdapter\Tests\Behavior\Features\Bootstrap\RedirectOperationTrait;

/**
 * Features context
 */
class FeatureContext extends \Behat\Behat\Context\BehatContext
{
    use NodeOperationsTrait;
    use IsolatedBehatStepsTrait;
    use RedirectOperationTrait;

    /**
     * @var \TYPO3\Flow\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Initializes the context
     *
     * @param array $parameters Context parameters (configured through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('flow', new \Flowpack\Behat\Tests\Behat\FlowContext($parameters));
        $this->objectManager = $this->getSubcontext('flow')->getObjectManager();
        $this->environment = $this->objectManager->get(Environment::class);
        $this->nodeTypeManager = $this->objectManager->get(NodeTypeManager::class);
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }
}
