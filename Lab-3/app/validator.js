const inpName = document.getElementById('name')
const inpSex = document.getElementById('sex');
const inpPhone = document.getElementById("phone");
const inpEmail = document.getElementById('email');
const inpIntership = document.getElementById('intership');
const inpLicense = document.getElementById("car_registration");
const inpTariffs = document.getElementById("tarifs");

const btnSubmit = document.getElementById("submit");
const form = document.querySelector("form");

btnSubmit.addEventListener("click", send_form);
form.addEventListener("submit", (event) => event.preventDefault());

class FormValidator {
  constructor(inputElement, inputValidator, valueValidator) {
    this.element = inputElement;
    let nxt = this.element.nextElementSibling
    // Если следущий элемент - метка для текущего
    this.hasInfo = false;
    if (nxt !== null){
      if (this.element.getAttribute("name") == nxt.getAttribute("for")){
        this.hasInfo = true;
      }
    }
    this.element.addEventListener("input", inputValidator);
    this.valueValidator = valueValidator;
  }

  validate() {
    let isValid = this.valueValidator(this.element.value);
    this.toggleError(!isValid);
    return isValid;
  }

  toggleError(bool) {
    if (bool){
      this.element.classList.add("invalid");
      if (this.hasInfo)
        this.element.nextElementSibling.classList.add("invalid-info");
    }
    else {
      this.element.classList.remove("invalid");
      if (this.hasInfo)
        this.element.nextElementSibling.classList.remove("invalid-info");
    }
  }
}

const inpNameValidator = new FormValidator(
  inpName,
  (event) => {
    let val = inpName.value;
    let m = val.match(/^(?:[a-zA-Zа-яА-Я]+\ ?)+/i);
    if (!m) {
      event.target.value = "";
    } else {
      event.target.value = m[0].substring(0, 50);
    }
  },
  (value) => value.length >= 4
);

const inpPhoneValidator = new FormValidator(
  inpPhone,
  (event) => {
    let val = inpPhone.value.replaceAll(/\D+/g, "").substring(0, 11);
    let m = val.match(/(\d)(\d{1,3})?(\d{1,3})?(\d{1,2})?(\d{1,2})?/);
    if (!m) {
      event.target.value = "";
    } else {
      event.target.value =
        `+${m[1]}` +
        (m[2] ? ` (${m[2]}` : "") +
        (m[3] ? `) ${m[3]}` : "") +
        (m[4] ? `-${m[4]}` : "") +
        (m[5] ? `-${m[5]}` : "");
    }
  },
  (value) => (value.replaceAll(/\D+/g, "").substring(0, 11).length == 11)
)

const inpEmailValidator = new FormValidator(
  inpEmail,
  (event) => {
    let val = inpEmail.value
    let m = val.match(/^(?:[a-zA-Z]\w*)(?:\.[a-zA-Z0-9]\w*)*@?(?:(?<=@)[a-zA-Z]*)?(?:(?<=[a-zA-Z])\.?[a-zA-Z]*)?/)
    if (!m) {
        event.target.value = ''
    } else {
        event.target.value = m[0]
    }
  },
  (value) => (/^[a-zA-Z]\S*@[a-zA-Z]+\.[a-zA-Z]+$/.test(value))
)

const inpIntershipValidator = new FormValidator(
  inpIntership,
  (_) => {},
  (value) => (value.length > 0)
)

const inpLicenseValidator = new FormValidator(
  inpLicense,
  (event) => {
    let val = inpLicense.value;
    let m = val.match(/[а-я0-9A-Z]{0,8}[ -]?[а-я0-9A-Z]{0,4}/i);
    if (!m) {
      event.target.value = "";
    } else {
      event.target.value = m[0];
    }
  },
  (value) => (value.length >= 4)
)

const inpTariffsValidator = new FormValidator(
  inpTariffs,
  (event) => {
    let val = inpTariffs.value;
    let m = val.match(/\d+/);
    if (!m || val.length == 0) {
      event.target.value = "";
    } else {
      event.target.value = m[0];
    }
  },
  (value) => (
    value.length >= 0 &&
    parseInt(value) >= 100 &&
    parseInt(value) <= 5000
  )
)

async function send_form() {
  // валидация введенных значений
  let isValid = true;

  [
    inpNameValidator,
    inpPhoneValidator,
    inpIntershipValidator,
    inpEmailValidator,
    inpLicenseValidator,
    inpTariffsValidator,
  ].forEach(validator => {
    isValid &= validator.validate()
  });
  // Если поля не корректны
  if (!isValid) return;

  await fetch("form.php", {
    method: "POST",
    body: new FormData(form),
  })
    .then(async (response) => {
      let res = await response.json();

      console.log(res);

      if (response.status == 400) {
        if (res["err"].match("INVALID NAME")) {
          inpNameValidator.toggleError(true);
        }
        if (res["err"].match("INVALID PHONE")) {
          inpPhoneValidator.toggleError(true);
        }
        if (res["err"].match("INVALID EMAIL")) {
          inpEmailValidator.toggleError(true);
        }
        if (res["err"].match("INVALID INTERSHIP")) {
          inpIntershipValidator.toggleError(true);
        }
        if (res["err"].match("NO SEX")) {
          alert("How?")
        }
        if (res["err"].match("INVALID LICENSE")) {
          inpLicenseValidator.toggleError(true);
        }
        if (res["err"].match("INVALID TARIFF")) {
          inpTariffsValidator.toggleError(true);
        }

        alert(res["err"] + res["msg"]);
      } else {
        alert(res["msg"]);
      }
      console.log(`Status: ${response.status}, error: ${res["err"]}`);
    })
    .catch((error) => {
      console.log(error["msg"]);
      alert("Internal Error");
    });
}
