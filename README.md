# Description

A small web application that allows to create user, login and manage articles through APIs accessible locally via https://zelty-test.local

## Getting Started

1. Add in the host file : 127.0.0.1 zelty-test.local
2. Run make start
3. Run make fix-perms
4. Run make vendor
5. Run make jwt-generate-keypair
6. Run make migrations
7. Run make database-test
8. Run make schema-test
9. Run make fixtures-load
10. Run make test-all

if necessary, check https://github.com/dunglas/symfony-docker/blob/main/docs/troubleshooting.md

## Features

* Create user
    - Endpoint : POST /api/register
    - Parameters : 
        - BODY
            - username,
            - password
    - Returns : ``` { "code": 200, "message": "Your account has been registered" } ```
    - Errors : ``` { "code": 400, "message": "validation message" } ```
    - Example : ``` curl -X POST https://zelty-test.local/api/register --data "{\"username\":\"test\", \"password\": \"test1234\"} ```

* Log-in user
    - Endpoint : POST /api/login_check
    - Parameters : ``` { "username": "test", "password": "password" } ```
    - Returns: ``` { "token": "JWT_ACCESS_TOKEN" } ```
    - Example : ``` curl -X POST https://zelty-test.local/api/login_check --data "{\"username\":\"test\", \"password\": \"test1234\"} ```

* Add an article
    - Endpoint : POST /api/article/new
    - Authentication : --header Authorization: Bearer <JWT_ACCESS_TOKEN>
    - Parameters : 
        - BODY
            - title, 
            - content, 
            - date_publication, 
            - status
        - QUERY
            - _format (json|xml)
    - Returns : ``` { "code": 200, "message": "Article created" } ```
    - Errors : ``` { "code": 400, "message": "validation message" } ```
    - Example : ``` curl -X POST https://zelty-test.local/api/article/new?_format=json --header "Authorization: Bearer <JWT_ACCESS_TOKEN>" --header "Content-Type: application/json" --data "{\"title\":\"test\", \"content\": \"test\", \"publication_date\": \"2022-09-24T12:15:00+00:00\", \"status\": \"published\"} ```

* Archive an article
    - Endpoint : PUT /api/article/{id}/archive
    - Authentication : --header Authorization: Bearer <JWT_ACCESS_TOKEN>
    - Parameters :
        - QUERY
            - _format (json|xml)
    - Returns : ``` { "code": 200, "message": "Article archived" } ```
    - Errors : ``` { "code": 404, "message": "Article not found" } ```
    - Example : ``` curl -X PUT https://zelty-test.local/api/article/1/archive?_format=json --header "Authorization: Bearer <JWT_ACCESS_TOKEN>" --header "Content-Type: application/json" ```

* List all articles
    - Endpoint : GET /api/articles/{page}
    - Authentication : --header Authorization: Bearer <JWT_ACCESS_TOKEN>
    - Parameters :
        - QUERY
            - _format (json|xml)
    - Returns : ``` { "code": 200, "data": [{ "id": 1, "title": "test", "content": "test", "author": "test", "publication_date": "2022-09-24T12:15:00+00:00", "status": "published" }] } ```
    - Example : ``` curl -X GET https://zelty-test.local/api/articles/1?_format=json --header "Authorization: Bearer <JWT_ACCESS_TOKEN>" --header "Content-Type: application/json" ```

* List all articles by status
    - Endpoint : GET /api/articles/{status}/{page}
    - Authentication : --header Authorization: Bearer <JWT_ACCESS_TOKEN>
    - Parameters :
        - QUERY
            - _format (json|xml)
    - Returns : ``` { "code": 200, "data": [{ "id": 1, "title": "test", "content": "test", "author": "test", "publication_date": "2022-09-24T12:15:00+00:00", "status": "published" }] } ```
    - Errors : ``` { "code": 400, "message": "error message" } ```
    - Example : ``` curl -X GET https://zelty-test.local/api/articles/draft/1?_format=json --header "Authorization: Bearer <JWT_ACCESS_TOKEN>" --header "Content-Type: application/json" ```