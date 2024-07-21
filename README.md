### Documant of the database: 
https://docs.google.com/document/d/e/2PACX-1vTqah2hdQeeiu3Le07zfOfp5vK-ojLwJtQmzbgdoq_wmJu-0dBdTcFsS0uSiUtYpSglEwMD5xSFIiG5/pub
### Setting Up and Running the Project in Docker:

1. **Create the `shopflow_network` network in Docker CLI:**
   ```bash
   sudo docker network create shopflow_network


2. **In the `infrastructure/docker`  directory, create a `.env`  file and fill in the values according to `.env.example`. Then run the following command to create the Postgres and Redis containers:**
   ```bash
   sudo docker compose up -d --build


3. **For creating the Admin containers, go to the `admin/docker`  directory, create a `.env`file and fill in the values according to `.env.example.` Then run the following command:**
   ```bash
   sudo docker compose up -d --build



4. **To set up the API project, follow the same steps as for the Admin project.**

**note:** Make sure to configure the database settings in the .env files for both Admin and API projects as follows, if you haven't changed the host and port in the Docker Compose files:
   ```bash
   DB_CONNECTION=pgsql
   DB_HOST=db
   DB_PORT=5432
```

Fill in the values for DB_DATABASE, DB_USERNAME, and DB_PASSWORD according to the .env file in the infrastructure directory.

