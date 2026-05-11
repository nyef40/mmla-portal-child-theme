#!/bin/bash
# Automated deployment script
# Save as deploy-auto.sh

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}Starting automated deployment...${NC}"

# Ensure we're on main and up to date
git checkout main
git pull origin main

# Create deployment tag
DEPLOY_TAG="auto-deploy-$(date +%Y%m%d-%H%M%S)"
git tag -a $DEPLOY_TAG -m "Automated deployment on $(date)"

# Push to GitHub
git push origin main
git push origin $DEPLOY_TAG

# Push to deployment server (triggers the hook)
git push deploy main

echo -e "${GREEN}Automated deployment completed!${NC}"
echo -e "${GREEN}Deployment tag: $DEPLOY_TAG${NC}"

# Check deployment log
echo -e "${YELLOW}Checking deployment log...${NC}"
ssh godaddy "tail -n 20 /home/c9gyjyiudq9m/deployment.log"