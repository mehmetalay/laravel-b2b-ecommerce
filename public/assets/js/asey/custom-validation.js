function addValidationRule(name, method, message) {
    $.validator.addMethod(name, method, message);
}

addValidationRule("phoneWithMaskFormat", function (value, element) {
    return this.optional(element) || /^\(\d{3}\) \d{3}-\d{4}$/.test(value);
}, "Lütfen geçerli bir telefon numarası giriniz.");

addValidationRule("fullNameValidation", function (value, element) {
    if (value) {
        value = value.trim().replace(/\s+/g, ' ');
    }
    return this.optional(element) || /^[a-zA-ZğüşıöçĞÜŞİÖÇ]+([-']?[a-zA-ZğüşıöçĞÜŞİÖÇ]+)*( [a-zA-ZğüşıöçĞÜŞİÖÇ]+([-']?[a-zA-ZğüşıöçĞÜŞİÖÇ]+)*)+$/.test(value);
}, "Ad ve soyadınızı eksiksiz giriniz.");

addValidationRule("validTC", function (value, element) {
    if (value === "") return true;
    if (!/^\d{11}$/.test(value)) return false;

    const digits = value.split('').map(Number);
    if (digits[0] === 0) return false;

    const sumOdd = digits[0] + digits[2] + digits[4] + digits[6] + digits[8];
    const sumEven = digits[1] + digits[3] + digits[5] + digits[7];
    const digit10 = ((sumOdd * 7) - sumEven) % 10;
    if (digit10 !== digits[9]) return false;

    const total = digits.slice(0, 10).reduce((a, b) => a + b, 0);
    const digit11 = total % 10;
    if (digit11 !== digits[10]) return false;

    return true;
}, "Geçerli bir T.C. Kimlik Numarası giriniz.");
