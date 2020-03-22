require('./bootstrap');
require('chart.js');

// Tagify
import Tagify from '@yaireo/tagify';

const uZips = document.getElementById('u-zip');
if (uZips) {
    const tagify = new Tagify(uZips);
}