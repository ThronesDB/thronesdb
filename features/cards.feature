Feature: Cards API
  I need to be able to get the cards data

  Scenario: I can query the /cards API endpoint by default 1.0 version
    When I request "/api/public/cards/" using HTTP GET
    Then the response code is 200
    When I load the response as JSON
    Then the JSON should be valid
    And the JSON should be valid according to the schema "cards-v1.0.json"

  Scenario: I can query the /cards API endpoint by explicit 1.0 version
    When I request "/api/public/cards/?v=1.0" using HTTP GET
    Then the response code is 200
    When I load the response as JSON
    Then the JSON should be valid
    And the JSON should be valid according to the schema "cards-v1.0.json"
