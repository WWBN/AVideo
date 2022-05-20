import babel from 'rollup-plugin-babel';
import pkg from './package.json';

export default [
  // Browser-friendly UMD build
  // CommonJS (for Node) and ES module (for bundlers) build.
  {
    input: 'lib/index.js',
    output: [
      { file: 'build/can-autoplay.js', format: 'umd', name: 'canAutoplay' },
      { file: pkg.main, format: 'cjs' },
      { file: pkg.module, format: 'es' }
    ],
    plugins: [
      babel({
        babelrc: false,
        exclude: ['node_modules/**'],
        presets: [
          ['env', {
            targets: {
              browsers: ['last 2 versions', 'safari >= 8', 'ie 11']
            },
            modules: false
          }]
        ]
      })
    ]
  }
];
