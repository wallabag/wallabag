Feature: Login 
	As a user 
	I want to login
	So that I can access my account entries
	
Background:
	Given the user has browsed to the login page

Scenario: Successfully Login
	When the user logs in as super admin 
	Then the user should be redirected to a page with the title 'Quickstart – wallabag'
	
Scenario Outline: Unsuccessful Login
	When the user logs in with username '<user>' and password '<password>' 
	Then the user should be redirected to a page with the title 'Welcome to wallabag! – wallabag'
	And an error message should be displayed saying "Invalid credentials."
	Examples:
	| user   | password |
	| bishal | rijal    |
	| admin  |          |
	| admin  | wrong    |
 