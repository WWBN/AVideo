# Google APIs PHP Client Generator

This directory is used to generate the client library service classes. 
See `synth.py` in the project root for more information.

From the root of this project run:

```
python2 -m pip install -e generator/ --user
```

Generate the client library with the following command

```
python2 -m googleapis.codegen \
  --output_dir=output \
  --input=generator/tests/testdata/foo.v1.json
```
