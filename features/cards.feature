Feature: Cards API
  I need to be able to get the cards data

  Scenario: I can query the /cards API endpoint
    When I request "/api/public/cards/" using HTTP GET
    Then the response code is 200
    When I load the response as JSON
    Then the JSON should be valid
    And the JSON should be valid according to the schema "cards.json"
