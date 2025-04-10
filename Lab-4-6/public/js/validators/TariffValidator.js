document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("form").addEventListener("submit", (event) => onSubmit(event));
    const nameInput = document.getElementById('name');
    const base_priceInput = document.getElementById('base_price');
    const base_distInput = document.getElementById('base_dist');
    const base_timeInput = document.getElementById('base_time');
    const dist_costInput = document.getElementById('dist_cost');
    const time_costInput = document.getElementById('time_cost');
    const csvFileInput = document.getElementById('csv-file');
    
    const nameValidator = new FormValidator(
        nameInput,
        (event) => {
            let val = event.target.value;
            let m = val.match(/\w{0,20}$/)
            if (!m) {
                event.target.value = "";
            } else {
                event.target.value = m[0];
            }
        },
        (value) => (/\w{3,20}$/.test(value)),
    )
    const base_priceValidator = new FormValidator(
        base_priceInput,
        BasicValidators.posFloatPassiveValidator,
        BasicValidators.posFloatActiveValidator
    )
    const base_distValidator = new FormValidator(
        base_distInput,
        BasicValidators.posFloatPassiveValidator,
        BasicValidators.posFloatActiveValidator
    )
    const base_timeValidator = new FormValidator(
        base_timeInput,
        BasicValidators.posFloatPassiveValidator,
        BasicValidators.posFloatActiveValidator
    )
    const dist_costValidator = new FormValidator(
        dist_costInput,
        BasicValidators.posFloatPassiveValidator,
        BasicValidators.posFloatActiveValidator
    )
    const time_costValidator = new FormValidator(
        time_costInput,
        BasicValidators.posFloatPassiveValidator,
        BasicValidators.posFloatActiveValidator
    )

    const csvFileValidator = new CSVValidator(csvFileInput)
})

function onSubmit(event) {
    let isValid = true;
    // Проверяем весь пак валидаторов
    [
        nameValidator,
        base_priceValidator,
        base_distValidator,
        base_timeValidator,
        dist_costValidator,
        time_costValidator,
    ].forEach(validator => {
        isValid &= validator.validate()
    })

    if (csvFileInput.files.length > 0){
        isValid = isValid || CSVValidator.validate();
    }

    if (!isValid) {
        event.preventDefault();
    };
}
