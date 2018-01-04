import uglify from 'rollup-plugin-uglify';
import multiEntry from 'rollup-plugin-multi-entry';

export default {
    input: [
        'assets/js/src/event-manager.js',
        'assets/js/src/front.js',
        'assets/js/src/front-facets.js'
    ],
    output: {
        file: 'assets/js/dist/front.min.js',
        format: 'iife',
        name: 'FWP_Build',
        strict: false
    },
    watch: {
        include: 'assets/js/src/**'
    },
    plugins: [
        multiEntry(),
        uglify()
    ]
}
