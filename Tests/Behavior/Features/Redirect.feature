Feature: Add redirect when a node is moved

  Background:
    Given I have the following nodes:
      | Identifier                           | Path                   | Node Type                  | Properties                                | Workspace | Hidden | Language      |
      | ecf40ad1-3119-0a43-d02e-55f8b5aa3c70 | /sites                 | unstructured               |                                           | live      |        |               |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/typo3cr         | TYPO3.Neos:Document        | {"uriPathSegment": "home"}                | live      |        |               |
      | 68ca0dcd-2afb-ef0e-1106-a5301e65b8a0 | /sites/typo3cr/company | TYPO3.Neos:Document        | {"uriPathSegment": "company"}             | live      |        |               |
      | 52540602-b417-11e3-9358-14109fd7a2dd | /sites/typo3cr/service | TYPO3.Neos:Document        | {"uriPathSegment": "service"}             | live      |        |               |
      | dc48851c-f653-ebd5-4d35-3feac69a3e09 | /sites/typo3cr/about   | TYPO3.Neos:Document        | {"uriPathSegment": "about"}               | live      |        |               |
      | 511e9e4b-2193-4100-9a91-6fde2586ae95 | /sites/typo3cr/imprint | TYPO3.Neos:Document        | {"uriPathSegment": "imprint"}             | live      | false  | de            |
    Given I have the following redirects:
      | sourceuripath                           | targeturipath      |
      | en/about.html                           | en/about-you.html  |

  @fixtures
  Scenario: Move a node into different node and a redirect will be created
    When I get a node by path "/sites/typo3cr/service" with the following context:
      | Workspace  |
      | user-admin |
    And I move the node into the node with path "/sites/typo3cr/company"
    And I publish the workspace "user-admin"
    Then A redirect should be created for the node with path "/sites/typo3cr/company/service" and with the following context:
      | Workspace  |
      | live       |
    And I should have a redirect with sourceUri "en/service.html" and TargetUri "en/company/service.html"

  @fixtures
  Scenario: Change the the `uriPathSegment` and a redirect will be created
    When I get a node by path "/sites/typo3cr/company" with the following context:
      | Workspace  |
      | user-admin |
    And I set the node property "uriPathSegment" to "evil-corp"
    And I publish the workspace "user-admin"
    Then A redirect should be created for the node with path "/sites/typo3cr/company" and with the following context:
      | Workspace  |
      | live       |
    And I should have a redirect with sourceUri "en/company.html" and TargetUri "en/evil-corp.html"

  # was fixed in 1.0.2
  @fixtures
  Scenario: Delete an existing redirect when the target URI matches the source URI of the new redirect
    When I get a node by path "/sites/typo3cr/about" with the following context:
      | Workspace  |
      | user-admin |
    And I set the node property "uriPathSegment" to "about-me"
    And I publish the workspace "user-admin"
    Then A redirect should be created for the node with path "/sites/typo3cr/company" and with the following context:
      | Workspace  |
      | live       |
    And I should have a redirect with sourceUri "en/about.html" and TargetUri "en/about-me.html"

  @fixtures
  Scenario:  I have an existing redirect for a node and I change the dimension the redirect should not be overwritten
    When I have the following redirects:
      | sourceuripath                           | targeturipath      |
      | imprint-from-the-old-site               | de/impressum.html  |
    When I get a node by path "/sites/typo3cr/imprint" with the following context:
      | Workspace  | Language |
      | user-admin | de    |
    And I set the node property "uriPathSegment" to "impressum"
    #And I make the node visible
    And I publish the workspace "user-admin"
    Then A redirect should be created for the node with path "/sites/typo3cr/imprint" and with the following context:
      | Workspace  | Language |
      | live       | de       |
    And I should have a redirect with sourceUri "de/imprint.html" and TargetUri "de/impressum.html"