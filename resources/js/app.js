require('./bootstrap');
require('chart.js');

// Tagify
import Tagify from '@yaireo/tagify';

const uZips = document.getElementById('u-zip');
if (uZips) {
    const tagify = new Tagify(uZips);
}

// AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});