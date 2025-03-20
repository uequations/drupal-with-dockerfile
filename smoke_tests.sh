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