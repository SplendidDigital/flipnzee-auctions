alert('watchlist.js loaded');

document.addEventListener('click', function (e) {

    if (!e.target.classList.contains('flipnzee-watchlist-button')) {
        return;
    }

    alert('BUTTON CLICKED');

    console.log(e.target);

});