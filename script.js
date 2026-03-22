document.querySelector('input[type="file"]').addEventListener('change', function(e){
    const file = e.target.files[0];
    const preview = document.createElement('img');

    preview.src = URL.createObjectURL(file);
    preview.style.width = "100%";

    document.body.appendChild(preview);
});

function excluir(id){
    if(confirm("Tem certeza que deseja excluir?")){
        window.location = "excluir_cardapio.php?id=" + id;
    }
}







const fileInput = document.getElementById('file');
const preview = document.getElementById('preview');

fileInput.addEventListener('change', function () {
    const file = this.files[0];

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    }
});


// MOSTRAR / OCULTAR SENHA
function toggleSenha() {
    const senha = document.getElementById("senha");
    senha.type = senha.type === "password" ? "text" : "password";
}

// LOADING NO BOTÃO
document.getElementById("formLogin")?.addEventListener("submit", function () {
    const btn = document.getElementById("btnLogin");
    btn.classList.add("loading");
    btn.innerText = "Entrando...";
});