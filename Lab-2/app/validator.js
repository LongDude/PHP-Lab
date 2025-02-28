const inpName = document.getElementById('name')
const inpPhone = document.getElementById('phone')
const inpRegistr = document.getElementById('car-registration')
const inpTariffs = document.getElementById('tarifs')
const btnSubmit = document.getElementById('submit')
const form = document.querySelector('form')

btnSubmit.addEventListener('click', send_form)
// Валидация на полях
let validForms = 0b0000
inpName.addEventListener('input', (event) => {
    let val = inpName.value
    let m = val.match(/^(?:[a-zA-Zа-яА-Я]+\ ?)+/)
    if (!m){
        event.target.value = ''
        validForms &= 0b0111
    } else {
        validForms |= 0b1000
        event.target.value = m[0].substring(0, 50)
    }
    toggleForm()
})

inpPhone.addEventListener('input', (event) => {
    let val = inpPhone.value.replaceAll(/\D+/g, '').substring(0, 11)
    let m = val.match(/(\d)(\d{1,3})?(\d{1,3})?(\d{1,2})?(\d{1,2})?/)
    if (!m){
        event.target.value = ''
        validForms &= 0b1011
    } else {
        validForms |= 0b0100
        event.target.valud = `+${m[1]}`
        + (m[2] ? ` (${m[2]}` : '')
        + (m[3] ? `) ${m[3]}` : '')
        + (m[4] ? `-${m[4]}` : '')
        + (m[5] ? `-${m[5]}` : '')
    }
    toggleForm()
})

inpRegistr.addEventListener('input', (event) => {
    let val = inpRegistr.value
    let m = val.match(/[A-Z0-9]/)
    if (!m){
        validForms &= 0b1101
        event.target.value = ''
    } else {
        validForms |= 0b0010
        event.target.value = $m[0]
    }
    toggleForm()
})

inpTariffs.addEventListener('input', (event) => {
    let val = inpTariffs.value
    let m = val.match(/\d+/)
    if (!m){
        validForms &= 0b1110
        event.target.value = ''
    } else {
        validForms |= 0b0001
        event.target.value = $m[0]
    }
    toggleForm()
})

function toggleForm(){
    if (validForms) {
        btnSubmit.removeAttribute('disabled')
    } else {
        btnSubmit.setAttribute('disabled', null)
    }
}

function send_form() {
    // валидация введенных значений
    let isValid = true

    inpName.classList.remove('invalid')
    inpName.nextElementSibling.classList.remove('invalid-info')

    inpPhone.classList.remove('invalid')
    inpPhone.nextElementSibling.classList.remove('invalid-info')

    inpRegistr.classList.remove('invalid')
    inpRegistr.nextElementSibling.classList.remove('invalid-info')

    inpTariffs.classList.remove('invalid')
    inpTariffs.nextElementSibling.classList.remove('invalid-info')

    // Проверка имени
    if (inpName.value.length < 4){
        isValid = false
        inpName.classList.add('invalid')
        inpName.nextElementSibling.classList.add('invalid-info')
    }

    // Проверка телефона
    if (inpPhone.value.replaceAll(/\D+/g, '').substring(0, 11).length < 11){
        isValid = false
        inpPhone.classList.add('invalid')
        inpPhone.nextElementSibling.classList.add('invalid-info')
    }

    // Проверка регистрационного номера
    if (inpRegistr.value.length < 4){
        isValid = false
        inpRegistr.classList.add('invalid')
        inpRegistr.nextElementSibling.classList.add('invalid-info')    
    }

    // проверка тарифа
    if (parseInt(inpTariffs.value) < 100 || parseInt(inpTariffs.value) > 5000){
        isValid = false
        inpTariffs.classList.add('invalid')
        inpTariffs.nextElementSibling.classList.add('invalid-info')        
    }
    
    // отправка формы
    if (!isValid) return

    fetch('form.php', {
        method: 'POST',
        body: new FormData(form)
    }).then((response) => {
        console.log(response)
        alert(response)
    }).catch((error) => {
        console.log(error)
        alert('Ошибка')
    })
}