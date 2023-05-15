# User Api

## Getting Started

1. Run `docker compose up -d` to start the Docker containers.
2. Run `sh ./application/mysql/import-data.sh` to initialize the MySQL database.
3. Open https://localhost:8002 in your favorite web browser and accept the auto-generated TLS certificate
4. Run `docker compose down` to stop the Docker containers.

### API Documentation:

Request Example:

`curl --location 'http://localhost:8002/users?is_active=true&is_member=false&user_type=2&last_login_at=2020-12-12%20to%202022-12-12'`

Response Example:

`{
    "data": [
        {
            "id": 328,
            "username": "test_328_user",
            "email": "test_328@tnc.com",
            "is_member": true,
            "is_active": true,
            "user_type": 1,
            "last_login_at": "2020-10-20 20:07:33"
        },
    ],
    "meta": 
    {
        "total": 9,
        "per_page": 10,
        "current_page": 1,
        "last_page": 1
    }
}`

### API Query Parameters:
* is_active: true/false,
* is_member: true/false,
* user_type: 1/2/3,
* last_login_at: 2020-12-12 to 2022-12-12
