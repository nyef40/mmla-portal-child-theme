
#!/bin/bash

# Get WordPress container name
WP_CONTAINER=$(/usr/local/bin/docker-compose ps -q wordpress)
echo 'WP_CONTAINER = ' $WP_CONTAINER
