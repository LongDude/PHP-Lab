const inpName = document.getElementById('name')
const inpPhone = document.getElementById('phone')
const inpRegistr = document.getElementById('car_registration')
const inpTariffs = document.getElementById('tarifs')
const btnSubmit = document.getElementById('submit')
const form = document.querySelector('form')

btnSubmit.addEventListener('click', send_form)
form.addEventListener('submit', (event) => event.preventDefault())
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
        event.target.value = `+${m[1]}`
        + (m[2] ? ` (${m[2]}` : '')
        + (m[3] ? `) ${m[3]}` : '')
        + (m[4] ? `-${m[4]}` : '')
        + (m[5] ? `-${m[5]}` : '')
    }
    toggleForm()
})

inpRegistr.addEventListener('input', (event) => {
    let val = inpRegistr.value
    let m = val.match(/[0-9A-Z]{1,8}/)
    if (!m){
        validForms &= 0b1101
        event.target.value = ''
    } else {
        validForms |= 0b0010
        event.target.value = m[0]
    }
    toggleForm()
})

inpTariffs.addEventListener('input', (event) => {
    let val = inpTariffs.value
    let m = val.match(/\d+/)
    if (!m || val.length == 0){
        validForms &= 0b1110
        event.target.value = ''
    } else {
        validForms |= 0b0001
        event.target.value = m[0]
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

async function send_form() {
    // валидация введенных значений
    let isValid = true

    inpName.classList.remove('invalid')
    inpName.nextElementSibling.classList.remove('invalid-info')

    inpPhone.classList.remove('invalid')

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
    }

    // Проверка регистрационного номера
    if (inpRegistr.value.length < 4){
        isValid = false
        inpRegistr.classList.add('invalid')
        inpRegistr.nextElementSibling.classList.add('invalid-info')    
    }

    // проверка тарифа
    if (inpTariffs.value.length <= 0 || parseInt(inpTariffs.value) < 100 || parseInt(inpTariffs.value) > 5000){
        isValid = false
        inpTariffs.classList.add('invalid')
        inpTariffs.nextElementSibling.classList.add('invalid-info')        
    }
    
    // отправка формы
    if (!isValid) return
    await fetch('form.php', {
        method: 'POST',
        body: new FormData(form)
    }).then(async (response) => {
        let res = await response.json()
        console.log(res)
        alert(res['msg'])
        console.log(`Status: ${response.status}, error: ${res['err']}`)
    }).catch((error) => {
        console.log(error)
        alert('Ошибка')
    })
}