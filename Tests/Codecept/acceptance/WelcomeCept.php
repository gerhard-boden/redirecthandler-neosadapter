<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Change the URI path segment and see a newly created redirect in the database');
$I->amOnPage('/neos');
$I->canSee('login');
$I->fillField('__authentication[TYPO3][Flow][Security][Authentication][Token][UsernamePassword][username]', 'gboden');
$I->fillField('__authentication[TYPO3][Flow][Security][Authentication][Token][UsernamePassword][password]', 'therealpassword');
$I->click('Login');
$I->see('Navigate');

$I->amGoingTo('en/features/shortcut/shortcut-to-child-node/down-the-rabbit-hole@user-gboden;language=en_US.html');
$I->fillField('//input[@id="ember2636"]', 'testUrl');