# Drupal on Docker

## Drupal on Docker Deployed to Azure App Services

### Git
Azure DevOps instance located at:https://dev.azure.com/uequations-2828/Drupal-With-Dockerfile/_build

### Deploy Latest Image
Go to the Deployment Center and update the tag number for the for the image. Once this is done and the change is saved, you won't need to restart the container to see the changes.

### Azure Blob File Storage
```
az storage account keys list --resource-group <resource-group-name> --account-name <storage-account-name>
```

### Azure SSH Into Container
```
$ az webapp create-remote-connection --subscription <subscription-id> --resource-group <resource-group-name> -n <app-name> &
```

If needed
```
az webapp config set --resource-group <resource-group-name> -n <app-name> --remote-debugging-enabled=false
```

### Logs
```
az webapp log tail --resource-group <resource-groups> --name <app-name>
```

## Drupal on Docker Deployed to Azure App Services

### Useful Commands

#### Useful Docker Commands

#### Authenticate with Google Cloud Artifact Docker Registry
```sh
gcloud auth configure-docker us-east4-docker.pkg.dev
```
This command will confiure your ~/.docker/config.json. Verify this file is updated correctly.

You may also need to run the command:
```sh
gcloud auth login
```

#### Build Docker image
Below are some examples of *docker build* commands.
```sh
docker build -t ubuntu-apache-httpd-drupal-4614:v1 .
```

```sh
docker build -t us-east4-docker.pkg.dev/dev-45627/uequations-docker-registry/ubuntu-apache-httpd-drupal-4614:v4 .
```
#### Push Docker Build to Registry
```sh
docker push us-east4-docker.pkg.dev/dev-45627/uequations-docker-registry/ubuntu-apache-httpd-drupal-4614:v4
```

### Running the Docker Image Locally
```sh
docker run -it us-east4-docker.pkg.dev/dev-45627/uequations-docker-registry/ubuntu-apache-httpd-drupal-4614:v4
```

#### Open Interactive Bash
```sh
docker exec -it e706b3fa81b1 bash
```

#### Useful gcloud commands
```sh
COMMIT_ID="$(git rev-parse --short=7 HEAD)"

gcloud builds submit --tag="${REGION}-docker.pkg.dev/${PROJECT_ID}/uequations-docker-registry/ubuntu-apache-httpd-php:v0.2" .

gcloud builds submit --region=us-east4 --tag="us-east4-docker.pkg.dev/dev-45627/uequations-docker-registry/ubuntu-apache-httpd-php:v0.2" .

docker push us-east4-docker.pkg.dev/dev-45627/uequations-docker-registry/ubuntu-apache-httpd-drupal-4614:v2
```

```sh
gcloud artifacts repositories list --project=dev-45627 \
--location=us-east4
```

```sh
gcloud artifacts repositories describe uequations-docker-registry  --project=dev-45627 --location=us-east4
```

### Service Accounts
560314436456-compute@developer.gserviceaccount.com

### Roles Needed
storage.objects.list => Storage Object Viewer (roles/storage.objectViewer)
roles/logging.logwriter => Logs Writer (roles/logging.logwriter)

## Common Errors

### Google Cloud
```
INFO: The service account running this build projects/dev-45627/serviceAccounts/560314436456-compute@developer.gserviceaccount.com does not have permission to write logs to Cloud Logging. To fix this, grant the Logs Writer (roles/logging.logWriter) role to the service account.
```

```
560314436456-compute@developer.gserviceaccount.com does not have storage.objects.list access to the Google Cloud Storage bucket. Permission 'storage.objects.list' denied on resource (or it may not exist).
```

## Memcache
### Memcache Default Settings
```php
  $settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];
  $settings['memcache']['bins'] = ['default' => 'default'];
  $settings['memcache']['key_prefix'] = '';
```

## Refences
- https://cloud.google.com/storage/docs/access-control/iam-roles
- https://cloud.google.com/logging/docs/access-control

```
The 'Domain Restricted Sharing' organization policy (constraints/iam.allowedPolicyMemberDomains) is enforced. Only principals in allowed domains can be added as principals in the policy. Correct the principal emails and try again. Learn more about domain restricted sharing.
```