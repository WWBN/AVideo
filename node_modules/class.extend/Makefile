REPORTER ?= dot

all: test

test: test-unit

test-unit: 
	mocha --reporter $(REPORTER) --growl test/*.test.js
