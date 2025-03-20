#!/bin/bash

# Test PHP
echo "Testing PHP..."
php --version

# Test Memcached
echo "Testing Memcached..."
memcached -h | grep "memcached"

# Test ImageMagick
echo "Testing ImageMagick..."
convert -version

# Test Redis
echo "Testing Redis..."
service redis-server start
redis-cli ping

# Test Apache2
# Check Apache service status
echo "Checking Apache service status..."
if systemctl is-active --quiet apache2; then
  echo "Apache is running."
else
  echo "Apache is not running."
  exit 1
fi

# Ping the localhost
echo "Pinging localhost..."
if curl -s http://localhost > /dev/null; then
  echo "Localhost is accessible."
else
  echo "Localhost is not accessible."
  exit 1
fi

# Verify Apache configuration
echo "Verifying Apache configuration..."
if apachectl configtest | grep -q "Syntax OK"; then
  echo "Apache configuration is OK."
else
  echo "Apache configuration has errors."
  exit 1
fi

# Review logs for critical errors
echo "Reviewing Apache error logs..."
if tail -n 10 /var/log/apache2/error.log | grep -i "error"; then
  echo "Errors found in Apache logs."
  exit 1
else
  echo "No critical errors in Apache logs."
fi

echo "Smoke test completed successfully."