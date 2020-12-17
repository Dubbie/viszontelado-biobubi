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

window.intToString = (value) => {
    const suffixes = ["", "e", "m", "b","t"];
    let suffixNum = Math.floor((""+value).length/3);
    let shortValue = parseFloat((suffixNum !== 0 ? (value / Math.pow(1000,suffixNum)) : value).toPrecision(2));
    if (shortValue % 1 !== 0) {
        shortValue = shortValue.toFixed(1);
    }
    return shortValue+suffixes[suffixNum];
};