# GitHub Actions CI/CD Setup Guide

This guide explains how to set up and use the GitHub Actions workflows for automated deployment.

## Overview

**Infrastructure** - `infrastructure.yml`
   - Manual trigger only
   - Creates/updates/deletes CloudFormation stack
   - Provisions complete AWS infrastructure

## Prerequisites

### Required GitHub Secrets

Configure the following secrets in your GitHub repository:

**Settings** → **Secrets and variables** → **Actions** → **New repository secret**

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `AWS_ACCOUNT_ID` | Your AWS account ID | `123456789012` |
| `AWS_ACCESS_KEY_ID` | AWS access key with appropriate permissions | `AKIA...` |
| `AWS_SECRET_ACCESS_KEY` | AWS secret access key | `secret...` |
| `CATALOG_APP_KEY` | Generate an App Key per service from local | php artisan key:generate --show
| `CHECKOUT_APP_KEY` | Generate an App Key per service from local | php artisan key:generate --show
| `EMAIL_APP_KEY` | Generate an App Key per service from local | php artisan key:generate --show
| `DB_PASSWORD` | Database master password | `StrongPassword123!` |
| `REDIS_PASSWORD` | Redis authentication password (min 16 chars) | `RedisPassword123!Min16` |
| `SMTP_HOST` | SMTP username for email service | `apikey` |
| `SMTP_PORT` | SMTP password | `SG.your-api-key` |
| `SMTP_USERNAME` | SMTP username for email service | `apikey` |
| `SMTP_PASSWORD` | SMTP password | `SG.your-api-key` |

### AWS IAM Permissions

The AWS credentials must have the following permissions:

- **ECR**: Full access to push/pull images
- **ECS**: Full access to manage services and tasks
- **CloudFormation**: Full access to create/update/delete stacks
- **RDS**: Create and manage database instances
- **ElastiCache**: Create and manage Redis clusters
- **VPC**: Create and manage networking resources
- **IAM**: Create roles and policies
- **Secrets Manager**: Create and read secrets
- **CloudWatch**: Create log groups and alarms

**Recommended**: Create an IAM user with `AdministratorAccess` policy for simplicity, or use a custom policy with the specific permissions listed above.

## Workflow Details

### Infrastructure Workflow (`infrastructure.yml`)

**Triggers**:
- Manual trigger only (intentionally safe)

**Actions**:
- `create` - Create new CloudFormation stack
- `update` - Update existing stack
- `delete` - Delete stack and all resources

**Jobs**:

1. **deploy-infrastructure**
   - Creates ECR repositories if needed
   - Updates parameter file with secrets
   - Validates CloudFormation template
   - Creates/updates/deletes stack based on action
   - Waits for operation to complete
   - Displays stack outputs

**Manual Trigger**:
1. Go to **Actions** tab
2. Select **Infrastructure - Deploy CloudFormation Stack**
3. Click **Run workflow**
4. Select action: `create`, `update`, or `delete`
5. Click **Run workflow**

## Complete Deployment Process

### First-Time Setup

#### Step 1: Configure GitHub Secrets

Add all required secrets as described in the Prerequisites section.

#### Step 2: Deploy Infrastructure

1. Go to **Actions** → **Infrastructure - Deploy CloudFormation Stack**
2. Click **Run workflow**
3. Select action: **create**
4. Click **Run workflow**
5. Wait ~15-20 minutes for completion
6. Check outputs for Application URL and resource details

#### Step 3: Initial Image Push

Since the stack requires existing Docker images, you need to push initial images:

```bash
# Run locally or trigger the deploy workflow manually
cd microservices-demo

# Set environment variables
export AWS_ACCOUNT_ID=<your-account-id>
export AWS_REGION=us-east-1

# Login to ECR
aws ecr get-login-password --region $AWS_REGION | \
  docker login --username AWS --password-stdin \
  $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com

# Build and push images
docker build -t $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/catalog:latest services/catalog
docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/catalog:latest

docker build -t $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/checkout:latest services/checkout
docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/checkout:latest

docker build -t $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/email:latest services/email
docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/email:latest

docker build -t $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/frontend:latest frontend
docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/cinch/frontend:latest
```

Or trigger the **CD - Deploy to AWS** workflow manually.

#### Step 4: Run Database Migrations

```bash
# Get ECS cluster name
CLUSTER_NAME=$(aws cloudformation describe-stacks \
  --stack-name cinch-infrastructure \
  --query 'Stacks[0].Outputs[?OutputKey==`ECSClusterName`].OutputValue' \
  --output text)

# Get task ARN for Catalog
CATALOG_TASK=$(aws ecs list-tasks \
  --cluster $CLUSTER_NAME \
  --service-name catalog-service \
  --query 'taskArns[0]' \
  --output text)

# Run migrations
aws ecs execute-command \
  --cluster $CLUSTER_NAME \
  --task $CATALOG_TASK \
  --container catalog \
  --interactive \
  --command "php artisan migrate --seed"

# Repeat for Checkout and Email services
```