 // Função para formatar o CNPJ
 function formatCNPJ(value) {
    let digits = value.replace(/\D/g, '');
    if (digits.length > 14) {
      digits = digits.substring(0, 14);
    }
    if (digits.length <= 2) {
      return digits;
    } else if (digits.length <= 5) {
      return digits.replace(/(\d{2})(\d+)/, "$1.$2");
    } else if (digits.length <= 8) {
      return digits.replace(/(\d{2})(\d{3})(\d+)/, "$1.$2.$3");
    } else if (digits.length <= 12) {
      return digits.replace(/(\d{2})(\d{3})(\d{3})(\d+)/, "$1.$2.$3/$4");
    } else {
      return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d+)/, "$1.$2.$3/$4-$5");
    }
  }
  
  // Evento para aplicar a formatação ao campo de CNPJ
  document.addEventListener("DOMContentLoaded", function(){
    const cnpjInput = document.getElementById('cnpj_filial');
    if (cnpjInput) {
      cnpjInput.addEventListener('input', function(){
        this.value = formatCNPJ(this.value);
      });
    }
  });
  
  // Função para formatar o telefone
  function formatPhone(value) {
    let digits = value.replace(/\D/g, '');
    if (digits.length === 0) return '';
    if (digits.length <= 2) {
      return digits;
    }
    let area = digits.substring(0, 2);
    let number = digits.substring(2);
    if (number.length <= 4) {
      return "(" + area + ") " + number;
    } else if (number.length <= 8) {
      return "(" + area + ") " + number.substring(0, 4) + "-" + number.substring(4);
    } else {
      return "(" + area + ") " + number.substring(0, 5) + "-" + number.substring(5, 9) +
             (number.length > 9 ? number.substring(9) : "");
    }
  }
  
  // Evento para aplicar a formatação ao campo de telefone
  document.addEventListener("DOMContentLoaded", function(){
    const contatoInput = document.getElementById('contato_responsavel');
    if (contatoInput) {
      contatoInput.addEventListener('input', function(){
        this.value = formatPhone(this.value);
      });
    }
  });
  
  // Função para formatar o CPF
  function formatCPF(value) {
    let digits = value.replace(/\D/g, '');
    if (digits.length > 11) {
      digits = digits.substring(0, 11);
    }
    if (digits.length <= 3) {
      return digits;
    } else if (digits.length <= 6) {
      return digits.replace(/(\d{3})(\d+)/, "$1.$2");
    } else if (digits.length <= 9) {
      return digits.replace(/(\d{3})(\d{3})(\d+)/, "$1.$2.$3");
    } else {
      return digits.replace(/(\d{3})(\d{3})(\d{3})(\d+)/, "$1.$2.$3-$4");
    }
  }
  
  // Evento para aplicar a formatação ao campo de CPF
  document.addEventListener("DOMContentLoaded", function(){
    const cpfInput = document.getElementById('cpf_responsavel');
    if (cpfInput) {
      cpfInput.addEventListener('input', function(){
        this.value = formatCPF(this.value);
      });
    }
  });
  
  // Inicialização dos selects com Select2
  function initSelects(){
    $(".select_tipo_de_base").select2({
      placeholder: l["selecione"],
      language: _lang,
      allowClear: true
    });
  }
  
  // Consulta de CEP e inicialização dos selects
  $(document).ready(function() {
    $('#btnConsultar').click(function(e) {
      e.preventDefault();
      var cep = $('#cep_filial').val().replace(/\D/g, '');
      if (cep !== "") {
        var validacep = /^[0-9]{8}$/;
        if (validacep.test(cep)) {
          $.getJSON("https://viacep.com.br/ws/" + cep + "/json/", function(data) {
            if (!("erro" in data)) {
              $('#rua_filial').val(data.logradouro);
              $('#cidade_filial').val(data.localidade);
              $('#estado_filial').val(data.uf);
              $('#pais_filial').val("Brasil");
              $('#latitude_filial').val("");
              $('#longitude_filial').val("");
              swal("Consulta realizada", "Consulta realizada com sucesso!", "success");
            } else {
              swal("Erro", "CEP não encontrado.", "error");
            }
          });
        } else {
          swal("Erro", "Formato de CEP inválido. Informe um CEP com 8 dígitos.", "error");
        }
      } else {
        swal("Erro", "Por favor, informe um CEP.", "error");
      }
    });
  
    initSelects();
  });