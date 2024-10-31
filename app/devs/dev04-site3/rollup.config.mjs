import terser from '@rollup/plugin-terser';

export default [
{
  // ADM    
  input: 'adm/src/app.js',
  output: [{
    file: 'adm/dist/app.js',
    format: 'iife',
    name:'App',
    plugins: [terser()],    
  }]
},{
  // ADM PLUGINS
  input: 'adm/src/app-plugins.js',
  output: {
    file: 'adm/dist/app-plugins.js',
    format: 'iife',
    name:'Plugins',
    plugins: [terser()],
  } 
},{
  // RDS
  input: 'rds/src/app.js',
  output: {
    file: 'rds/dist/app.js',
    format: 'iife',
    name:'App',
    plugins: [terser()],
  }
},{
  // SITE
  input: 'site/src/app.js',
  output: {
    file: 'site/dist/app.js',
    format: 'iife',
    name:'App',    
    plugins: [terser()],    
  },
},{
  // PBL
  external: ['jquery'],
  input: 'pbl/src/app.js',
  output: {
    file: 'pbl/dist/app.js',
    format: 'iife',
    name:'App',    
    globals: {'jquery': 'jQuery'},
    plugins: [terser()],    
  }    
}
];


