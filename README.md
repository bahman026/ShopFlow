### Documant of the database: 
https://docs.google.com/document/d/e/2PACX-1vTqah2hdQeeiu3Le07zfOfp5vK-ojLwJtQmzbgdoq_wmJu-0dBdTcFsS0uSiUtYpSglEwMD5xSFIiG5/pub
### Setting Up and Running the Project in Docker:



**In the `infrastructure/docker`  directory, create a `.env`  file and fill in the values according to `.env.example`. Then run the following command to create the Postgres and Redis containers:**
   ```bash
   sudo docker compose up -d --build


**note:** Make sure to configure the database settings in the .env files for both Admin and API projects as follows, if you haven't changed the host and port in the Docker Compose files:
   ```bash
   DB_CONNECTION=pgsql
   DB_HOST=db
   DB_PORT=5432
```

Fill in the values for DB_DATABASE, DB_USERNAME, and DB_PASSWORD according to the .env file in the infrastructure directory.

