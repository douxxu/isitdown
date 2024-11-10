document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    const url = document.querySelector('input[name="url"]').value;
    const resultDiv = document.querySelector('.result');
    const checkingDiv = document.querySelector('.checking');

    checkingDiv.style.display = 'inline-block';
    resultDiv.innerHTML = '';

    const timeout = 5000;
    let timeoutReached = false;

    const timeoutId = setTimeout(() => {
        timeoutReached = true;
        checkingDiv.style.display = 'none';
        resultDiv.classList.add('down');
        resultDiv.classList.remove('up');
        resultDiv.innerHTML = `<p><strong>The site is <span class="down">down</span>.</strong></p>
                               <h6>Response time exceeded the 5-second limit.</h6>`;
    }, timeout);

    fetch('api/check.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ url: url })
    })
    .then(response => response.json())
    .then(data => {
        if (timeoutReached) return;

        clearTimeout(timeoutId);
        checkingDiv.style.display = 'none';

        if (data.status === 'up') {
            resultDiv.classList.add('up');
            resultDiv.classList.remove('down');
            resultDiv.innerHTML = `<p>The site is <strong>online</strong>!</p>
                                   <p>Response time: <strong>${data.ping} ms</strong></p>
                                   <p><strong>Additional information:</strong></p>
                                   <ul>`;
            for (let key in data.headers) {
                resultDiv.innerHTML += `<li><strong>${key}:</strong> ${Array.isArray(data.headers[key]) ? data.headers[key].join(', ') : data.headers[key]}</li>`;
            }
            resultDiv.innerHTML += `</ul>`;
        } else {
            resultDiv.classList.add('down');
            resultDiv.classList.remove('up');
            resultDiv.innerHTML = `<p><strong>The site is <span class="down">down</span>.</strong></p>`;
            
            if (data.message) {
                resultDiv.innerHTML += `<h6>${data.message}</h6>`;
            }
        }
    })
    .catch(error => {
        checkingDiv.style.display = 'none';
        console.log(error);
    });
});
