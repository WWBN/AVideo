#!/bin/sh
# Get ApiGen.phar
wget http://www.apigen.org/apigen.phar

# Generate SDK Docs
php apigen.phar generate --template-theme="bootstrap" -s lib -d ../gh-pages/docs

# Copy Home Page from Master Branch to Gh-Pages folder
cp -r docs/* ../gh-pages/

# Copy samples
cp -r sample ../gh-pages/sample
# As PHP is not allowed in Github
cp sample/index.php ../gh-pages/sample/index.html

cd ../gh-pages

# Set identity
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

# Add branch
git init
git remote add origin https://${GH_TOKEN}@github.com/paypal/PayPal-PHP-SDK.git > /dev/null
git checkout -B gh-pages

# Push generated files
git add .
git commit -m "Docs updated by Travis"
git push origin gh-pages -fq > /dev/null
