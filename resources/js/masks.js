// Máscara para CPF: 000.000.000-00
function maskCPF(value) {
    value = value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    return value;
}

// Máscara para PIS/PASEP: 000.00000.00-0
function maskPIS(value) {
    value = value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{5})(\d)/, '$1.$2');
    value = value.replace(/(\d{2})(\d{1})$/, '$1-$2');
    return value;
}

// Máscara para CNPJ: 00.000.000/0000-00
function maskCNPJ(value) {
    value = value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1/$2');
    value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    return value;
}

// Máscara para telefone: (00) 00000-0000 ou (00) 0000-0000
function maskPhone(value) {
    value = value.replace(/\D/g, '');
    if (value.length <= 10) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
    }
    return value;
}

// Máscara para CEP: 00000-000
function maskCEP(value) {
    value = value.replace(/\D/g, '');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    return value;
}

// Aplicar máscaras quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // CPF
    const cpfInputs = document.querySelectorAll('input[name="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            e.target.value = maskCPF(e.target.value);
        });
    });

    // PIS/PASEP
    const pisInputs = document.querySelectorAll('input[name="pis_pasep"]');
    pisInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            e.target.value = maskPIS(e.target.value);
        });
    });

    // CNPJ
    const cnpjInputs = document.querySelectorAll('input[name="cnpj"]');
    cnpjInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            e.target.value = maskCNPJ(e.target.value);
        });
    });

    // Telefone
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            e.target.value = maskPhone(e.target.value);
        });
    });

    // CEP
    const cepInputs = document.querySelectorAll('input[name="zip_code"]');
    cepInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            e.target.value = maskCEP(e.target.value);
        });
    });
});
