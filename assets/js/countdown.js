document.addEventListener('DOMContentLoaded', function () {

    const timers = document.querySelectorAll('.flipnzee-countdown');

    timers.forEach(function (timer) {

        const endTime = new Date(timer.dataset.end).getTime();

        function updateCountdown() {

            const now = new Date().getTime();

            const distance = endTime - now;

            if (distance <= 0) {
    timer.textContent = 'Auction Ended';
    timer.classList.add('ended');
    return;
}

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));

            const hours = Math.floor(
                (distance % (1000 * 60 * 60 * 24))
                / (1000 * 60 * 60)
            );

            const minutes = Math.floor(
                (distance % (1000 * 60 * 60))
                / (1000 * 60)
            );

            const seconds = Math.floor(
                (distance % (1000 * 60))
                / 1000
            );

            timer.textContent =
    days + 'd ' +
    hours + 'h ' +
    minutes + 'm ' +
    seconds + 's';
        }

        updateCountdown();

        setInterval(updateCountdown, 1000);

    });

});