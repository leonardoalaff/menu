<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: painel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardápioOn | Cardápio digital para restaurantes, lanchonetes e delivery</title>
  <meta name="description" content="Crie seu cardápio digital profissional, organize produtos, destaque imagens, atualize preços em segundos e compartilhe com seus clientes.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style_index6.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="landing-body">

  <header class="topbar" id="topbar">
    <div class="container topbar-inner">
      <a href="#" class="brand">
        <div class="brand-logo"></div>
      </a>

      <nav class="topbar-nav">
        <a href="#beneficios">Benefícios</a>
        <a href="#funcionalidades">Funcionalidades</a>
        <a href="#como-funciona">Como funciona</a>
        <a href="#faq">FAQ</a>
      </nav>

      <div class="topbar-actions">
        <a href="#login" class="btn-outline">Entrar</a>
      </div>
    </div>
  </header>

  <main>

    <section class="hero-section">
      <div class="hero-parallax hero-orb hero-orb-1" data-speed="0.18"></div>
      <div class="hero-parallax hero-orb hero-orb-2" data-speed="0.12"></div>
      <div class="hero-parallax hero-orb hero-orb-3" data-speed="0.22"></div>

      <div class="container hero-grid">
        <div class="hero-copy reveal-left" data-speed="0.08">
          <span class="section-badge">Cardápio digital para vender mais</span>

          <h1>
            Tenha um <span>cardápio online profissional</span> para seu restaurante,
            lanchonete ou delivery
          </h1>

          <p class="hero-text">
            Organize seus produtos, destaque imagens, edite preços rapidamente e entregue
            uma experiência moderna para seus clientes no celular.
          </p>

          <div class="hero-checks">
            <span><i class="ri-check-line"></i> Visual moderno e profissional</span>
            <span><i class="ri-check-line"></i> Atualização rápida de itens e preços</span>
            <span><i class="ri-check-line"></i> Experiência pensada para celular</span>
          </div>

          <div class="hero-buttons">
            <a href="cadastrar.php" class="btn-primary btn-lg">Criar minha conta</a>
            <a href="#demonstracao" class="btn-secondary btn-lg">Ver demonstração</a>
          </div>

          <div class="hero-mini-proof">
            <div class="mini-proof-card reveal delay-1">
              <strong>Mais presença digital</strong>
              <p>Seu negócio mais bonito e mais confiável online.</p>
            </div>

            <div class="mini-proof-card reveal delay-2">
              <strong>Mais praticidade</strong>
              <p>Edite o cardápio em poucos minutos sempre que precisar.</p>
            </div>

            <div class="mini-proof-card reveal delay-3">
              <strong>Mais conversão</strong>
              <p>Produtos melhor apresentados ajudam o cliente a decidir.</p>
            </div>
          </div>
        </div>

        <div class="hero-visual reveal-right delay-1 hero-parallax" data-speed="0.1" id="demonstracao">
          <div class="dashboard-card">
            <div class="dashboard-top">
              <div>
                <span class="small-tag">Painel CardápioOn</span>
                <h3>Seu cardápio com aparência premium</h3>
              </div>
              <div class="status-chip">
                <i class="ri-smartphone-line"></i>
                Mobile Ready
              </div>
            </div>

            <div class="dashboard-preview">
              <div class="phone-card">
                <div class="phone-header">
                  <div class="phone-logo"></div>
                  <div>
                    <strong>Meu Negócio</strong>
                    <span>Cardápio digital</span>
                  </div>
                </div>

                <div class="menu-list">
                  <div class="menu-item">
                    <div class="menu-thumb thumb-1"></div>
                    <div class="menu-info">
                      <strong>X-Burguer Especial</strong>
                      <p>Pão brioche, hambúrguer artesanal e molho da casa</p>
                    </div>
                    <b>R$ 24,90</b>
                  </div>

                  <div class="menu-item">
                    <div class="menu-thumb thumb-2"></div>
                    <div class="menu-info">
                      <strong>Pizza Calabresa</strong>
                      <p>Massa leve, queijo e cobertura generosa</p>
                    </div>
                    <b>R$ 39,90</b>
                  </div>

                  <div class="menu-item">
                    <div class="menu-thumb thumb-3"></div>
                    <div class="menu-info">
                      <strong>Combo Batata + Refri</strong>
                      <p>Ideal para aumentar ticket médio</p>
                    </div>
                    <b>R$ 17,90</b>
                  </div>
                </div>
              </div>

              <div class="feature-stack">
                <div class="feature-note">
                  <i class="ri-image-line"></i>
                  <div>
                    <strong>Produtos com imagem</strong>
                    <p>Mais apelo visual para vender melhor</p>
                  </div>
                </div>

                <div class="feature-note">
                  <i class="ri-edit-line"></i>
                  <div>
                    <strong>Edição simples</strong>
                    <p>Atualize itens, descrições e preços com facilidade</p>
                  </div>
                </div>

                <div class="feature-note">
                  <i class="ri-layout-grid-line"></i>
                  <div>
                    <strong>Organização por categorias</strong>
                    <p>Cardápio mais claro e agradável para o cliente</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="proof-strip">
      <div class="container proof-strip-inner">
        <span class="proof-title">Ideal para negócios como:</span>
        <div class="proof-tags">
          <span>Hamburguerias</span>
          <span>Pizzarias</span>
          <span>Lanchonetes</span>
          <span>Açaíterias</span>
          <span>Restaurantes</span>
          <span>Delivery</span>
        </div>
      </div>
    </section>

    <section class="section" id="beneficios">
      <div class="container">
        <div class="section-head center reveal">
          <span class="section-badge">Benefícios</span>
          <h2>Uma presença digital mais profissional para o seu negócio</h2>
          <p>
            O CardápioOn foi pensado para transformar a apresentação dos seus produtos
            em algo mais bonito, organizado e fácil de atualizar.
          </p>
        </div>

        <div class="benefits-grid">
          <article class="benefit-card reveal delay-1">
            <div class="benefit-icon"><i class="ri-layout-4-line"></i></div>
            <h3>Visual profissional</h3>
            <p>Apresente seus produtos com mais organização e mais valor percebido.</p>
          </article>

          <article class="benefit-card reveal delay-2">
            <div class="benefit-icon"><i class="ri-smartphone-line"></i></div>
            <h3>Feito para celular</h3>
            <p>Seu cardápio fica bonito em smartphones, onde a maioria dos clientes acessa.</p>
          </article>

          <article class="benefit-card reveal delay-3">
            <div class="benefit-icon"><i class="ri-edit-2-line"></i></div>
            <h3>Fácil de editar</h3>
            <p>Atualize preços, descrições, categorias e imagens sem complicação.</p>
          </article>

          <article class="benefit-card reveal delay-1">
            <div class="benefit-icon"><i class="ri-image-2-line"></i></div>
            <h3>Mais apelo visual</h3>
            <p>Fotos dos produtos ajudam a despertar desejo e melhorar a conversão.</p>
          </article>

          <article class="benefit-card reveal delay-2">
            <div class="benefit-icon"><i class="ri-price-tag-3-line"></i></div>
            <h3>Mais clareza nos preços</h3>
            <p>Facilite a decisão do cliente com uma apresentação limpa e objetiva.</p>
          </article>

          <article class="benefit-card reveal delay-3">
            <div class="benefit-icon"><i class="ri-rocket-line"></i></div>
            <h3>Mais velocidade</h3>
            <p>Coloque seu cardápio no ar e mantenha tudo atualizado com rapidez.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-soft" id="funcionalidades">
      <div class="container split-grid">
        <div class="split-image image-panel image-panel-one reveal-left"></div>

        <div class="split-copy reveal-right">
          <span class="section-badge">Funcionalidades</span>
          <h2>Organize seus produtos de forma clara e moderna</h2>
          <p>
            Mostre categorias, descrições, preços e imagens em uma interface muito mais
            agradável para o cliente navegar.
          </p>

          <div class="check-list">
            <div class="check-item"><i class="ri-check-line"></i><span>Categorias bem organizadas</span></div>
            <div class="check-item"><i class="ri-check-line"></i><span>Produtos com imagem e descrição</span></div>
            <div class="check-item"><i class="ri-check-line"></i><span>Visual limpo e profissional</span></div>
            <div class="check-item"><i class="ri-check-line"></i><span>Mais destaque para os itens principais</span></div>
          </div>

          <a href="cadastrar.php" class="btn-primary inline-btn">Começar agora</a>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container split-grid reverse">
        <div class="split-copy reveal-left">
          <span class="section-badge">Atualização rápida</span>
          <h2>Edite o cardápio sempre que precisar</h2>
          <p>
            Mudou preço? Entrou item novo? Acabou um produto? Com o CardápioOn você
            atualiza o cardápio com mais praticidade e mantém tudo certo para seus clientes.
          </p>

          <div class="check-list">
            <div class="check-item"><i class="ri-check-line"></i><span>Alteração rápida de preços</span></div>
            <div class="check-item"><i class="ri-check-line"></i><span>Edição simples de descrições</span></div>
            <div class="check-item"><i class="ri-check-line"></i><span>Inclusão de novos itens</span></div>
            <div class="check-item"><i class="ri-check-line"></i><span>Mais controle no dia a dia</span></div>
          </div>

          <a href="#login" class="btn-outline inline-btn">Entrar no painel</a>
        </div>

        <div class="split-image image-panel image-panel-two reveal-right"></div>
      </div>
    </section>

    <section class="section section-soft" id="como-funciona">
      <div class="container">
        <div class="section-head center reveal">
          <span class="section-badge">Como funciona</span>
          <h2>Comece em poucos passos</h2>
          <p>
            Uma estrutura simples para você criar seu cardápio digital e compartilhar com seus clientes.
          </p>
        </div>

        <div class="steps-grid">
          <article class="step-card reveal delay-1">
            <div class="step-number">01</div>
            <h3>Crie sua conta</h3>
            <p>Cadastre-se e acesse seu painel administrativo.</p>
          </article>

          <article class="step-card reveal delay-2">
            <div class="step-number">02</div>
            <h3>Adicione seus produtos</h3>
            <p>Cadastre nome, descrição, categoria, preço e imagem.</p>
          </article>

          <article class="step-card reveal delay-3">
            <div class="step-number">03</div>
            <h3>Personalize o visual</h3>
            <p>Deixe o cardápio com a aparência do seu negócio.</p>
          </article>

          <article class="step-card reveal delay-4">
            <div class="step-number">04</div>
            <h3>Compartilhe</h3>
            <p>Envie o link do cardápio para seus clientes e divulgue online.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head center reveal">
          <span class="section-badge">Prova social</span>
          <h2>Uma experiência melhor para o cliente perceber mais valor no seu negócio</h2>
          <p>
            Um cardápio bonito, claro e moderno transmite mais profissionalismo e ajuda o cliente a comprar.
          </p>
        </div>

        <div class="testimonials-grid">
          <article class="testimonial-card reveal-zoom delay-1">
            <div class="stars">
              <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
            </div>
            <p>“Agora nosso cardápio passa muito mais profissionalismo. Os produtos ficaram mais valorizados.”</p>
            <strong>Hamburgueria</strong>
          </article>

          <article class="testimonial-card reveal-zoom delay-2">
            <div class="stars">
              <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
            </div>
            <p>“A possibilidade de editar rápido ajuda muito quando mudamos preço ou lançamos novidade.”</p>
            <strong>Delivery</strong>
          </article>

          <article class="testimonial-card reveal-zoom delay-3">
            <div class="stars">
              <i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i><i class="ri-star-fill"></i>
            </div>
            <p>“No celular ficou excelente. Mais bonito, mais organizado e fácil de navegar.”</p>
            <strong>Pizzaria</strong>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-soft" id="login">
      <div class="container cta-login-grid">
        <div class="cta-copy reveal-left">
          <span class="section-badge">Comece agora</span>
          <h2>Crie sua conta e tenha seu cardápio digital com aparência profissional</h2>
          <p>
            Entre no CardápioOn e monte uma apresentação mais bonita, moderna e pronta
            para destacar seus produtos.
          </p>

          <div class="cta-points">
            <span><i class="ri-check-line"></i> Cadastro simples</span>
            <span><i class="ri-check-line"></i> Painel intuitivo</span>
            <span><i class="ri-check-line"></i> Layout voltado para conversão</span>
          </div>
        </div>

        <form class="form-card landing-form reveal-right" action="login.php" method="POST">
          <div class="form-header">
            <h2>Entrar</h2>
            <p class="form-subtitle">Acesse seu painel e atualize seu cardápio quando quiser.</p>
          </div>

          <div class="input-group">
            <label for="email">E-mail</label>
            <div class="input-wrap">
              <span class="input-icon"><i class="ri-mail-line"></i></span>
              <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>
          </div>

          <div class="input-group">
            <label for="senha">Senha</label>
            <div class="input-wrap">
              <span class="input-icon"><i class="ri-lock-2-line"></i></span>
              <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
              <button type="button" class="toggle-password" onclick="toggleSenha()">
                <i class="ri-eye-line"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-primary full-btn" id="btnLogin">Entrar</button>
          <a href="cadastrar.php" class="link-btn">Criar minha conta</a>
        </form>
      </div>
    </section>

    <section class="section" id="faq">
      <div class="container">
        <div class="section-head center reveal">
          <span class="section-badge">FAQ</span>
          <h2>Perguntas frequentes</h2>
          <p>Algumas dúvidas comuns antes de começar.</p>
        </div>

        <div class="faq-list">
          <article class="faq-item reveal delay-1">
            <button class="faq-question" type="button">
              <span>Consigo editar meu cardápio depois?</span>
              <i class="ri-add-line"></i>
            </button>
            <div class="faq-answer">
              <p>Sim. Você pode alterar itens, preços, descrições, categorias e imagens sempre que quiser.</p>
            </div>
          </article>

          <article class="faq-item reveal delay-2">
            <button class="faq-question" type="button">
              <span>O cardápio funciona bem no celular?</span>
              <i class="ri-add-line"></i>
            </button>
            <div class="faq-answer">
              <p>Sim. O CardápioOn foi pensado para entregar uma experiência muito boa em smartphones.</p>
            </div>
          </article>

          <article class="faq-item reveal delay-3">
            <button class="faq-question" type="button">
              <span>Posso adicionar imagens nos produtos?</span>
              <i class="ri-add-line"></i>
            </button>
            <div class="faq-answer">
              <p>Sim. Imagens ajudam a valorizar os produtos e deixar o cardápio mais atrativo.</p>
            </div>
          </article>

          <article class="faq-item reveal delay-4">
            <button class="faq-question" type="button">
              <span>É difícil configurar?</span>
              <i class="ri-add-line"></i>
            </button>
            <div class="faq-answer">
              <p>Não. A ideia é justamente ser simples de usar e rápido para colocar no ar.</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="final-cta">
      <div class="container final-cta-box reveal-zoom">
        <div>
          <span class="section-badge light-badge">CardápioOn</span>
          <h2>Seu negócio merece um cardápio mais bonito, mais moderno e mais profissional</h2>
          <p>Crie sua conta e comece agora.</p>
        </div>

        <a href="cadastrar.php" class="btn-light">Criar minha conta</a>
      </div>
    </section>

  </main>

  <script>
    function toggleSenha() {
      const senhaInput = document.getElementById('senha');
      const icone = document.querySelector('.toggle-password i');

      if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        icone.className = 'ri-eye-off-line';
      } else {
        senhaInput.type = 'password';
        icone.className = 'ri-eye-line';
      }
    }

    document.querySelector(".landing-form").addEventListener("submit", function() {
      const btn = document.getElementById("btnLogin");
      btn.textContent = "Entrando...";
      btn.disabled = true;
    });

    document.querySelectorAll('.faq-question').forEach(button => {
      button.addEventListener('click', () => {
        button.parentElement.classList.toggle('active');
      });
    });

    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-zoom');

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('show');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px'
    });

    revealElements.forEach((el) => observer.observe(el));

    const topbar = document.getElementById('topbar');
    const heroParallax = document.querySelectorAll('.hero-parallax');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function handleScrollEffects() {
      const scrollY = window.scrollY;

      if (scrollY > 24) {
        topbar.classList.add('scrolled');
      } else {
        topbar.classList.remove('scrolled');
      }

      if (!reduceMotion && window.innerWidth > 768) {
        heroParallax.forEach((el) => {
          const speed = parseFloat(el.dataset.speed || '0.1');
          el.style.transform = `translate3d(0, ${scrollY * speed}px, 0)`;
        });
      }
    }

    handleScrollEffects();
    window.addEventListener('scroll', handleScrollEffects, { passive: true });
  </script>
</body>
</html>