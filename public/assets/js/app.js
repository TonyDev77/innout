// Função par adicionar ou remover a classe 'hide-sidebar' na tag <body>
(function () { // Função auto-invocada
    const menuToggle = document.querySelector('.menu-toggle')
    menuToggle.onclick = function (e) {
        const body = document.querySelector('body')
        body.classList.toggle('hide-sidebar')
    }
})()

// Incrementa segundos no relógio
function addOneSecond(hours, minutes, seconds) {
    // Converte a string pra Date e a incrementa
    const d = new Date()
    d.setHours(parseInt(hours))
    d.setMinutes(parseInt(minutes))
    d.setSeconds(parseInt(seconds) + 1)

    // Converte de volta pra String e a envia de volta
    const h = `${d.getHours()}`.padStart(2, 0)
    const m = `${d.getMinutes()}`.padStart(2, 0)
    const s = `${d.getSeconds()}`.padStart(2, 0)

    return `${h}:${m}:${s}`
}


// Função para incrementar os relógios de left.php
function activateClock() {
    const activeClock = document.querySelector('[active-clock]')
    if (!activeClock)
        return

    // Função nativa que recebe outra função e um tempo para identificar o objeto a ser incrementado
    setInterval(function () {
        const parts = activeClock.innerHTML.split(':')
        activeClock.innerHTML = addOneSecond(parts[0], parts[1], parts[2])
    }, 1000) // incrementa em 1000ms = 1seg
}
activateClock()