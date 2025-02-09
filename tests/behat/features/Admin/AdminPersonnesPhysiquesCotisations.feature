Feature: Administration - Partie Personnes physiques - cotisations

  @reloadDbWithTestData
  Scenario: On test le nom du fichier PDF de cotisation récupéré depuis l'admin d'une personne physique
    Given I am logged in as admin and on the Administration
    And I follow "Personnes physiques"
    And I check "alsoDisplayInactive"
    And I press "Filtrer"
    Then I should see "userexpire"
    When I follow "cotisations_2"
    Then I should see "Cotisations de Jean Maurice"
    When I follow the button of tooltip "Télécharger la facture"
    Then the response header "Content-disposition" should equal 'attachment; filename="Maurice_COTIS-2018-198_13072018.pdf"'

  @reloadDbWithTestData
  Scenario: On test le nom du fichier PDF de cotisation récupéré depuis l'admin d'une personne morale
    Given I am logged in as admin and on the Administration
    And I follow "Personnes morales"
    And I check "also_display_inactive"
    And I press "Filtrer"
    Then I should see "MyCorp"
    When I follow the button of tooltip "Gérer les cotisations de MyCorp"
    Then I should see "Cotisations de MyCorp"
    When I follow the button of tooltip "Télécharger la facture"
    Then the response header "Content-disposition" should equal 'attachment; filename="MyCorp_COTIS-2018-201_13072018.pdf"'
