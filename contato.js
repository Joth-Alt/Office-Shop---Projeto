// ====================================================================
// contato.js: Lógica do Chat Bot Inteligente (Versão Final com Easter Egg "Aura")
// ====================================================================

document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');
    const micBtn = document.getElementById('mic-btn');

    // Estado do Bot e Memória da Sessão
    let botState = 'INICIO'; 
    let sessionData = {
        userName: null,
        lastTopic: null,
        lastQuestion: null,
        emailForm: {}
    }; 

    // --- FUNÇÃO AUXILIAR: Adicionar Mensagem à Tela ---
    function addMessage(text, sender) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        
        // Substitui quebras de linha por <br> e permite tags <a> e <img>
        messageElement.innerHTML = text.replace(/\n/g, '<br>');

        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // ====================================================================
    // FUNÇÃO RPG: Rolagem de Dados (X d Y)
    // ====================================================================
    function rollDice(input) {
        // Expressão regular para encontrar o padrão XdY (Ex: 2d10, 1d20, d6)
        const match = input.match(/(\d*)\s*[dD]\s*(\d+)/);

        if (!match) {
            return null; // Não encontrou o padrão de rolagem de dado
        }

        let numDice = parseInt(match[1] || '1', 10); // Quantidade de dados (padrão é 1 se vazio)
        const dieType = parseInt(match[2], 10);      // Tipo do dado (d4, d6, d20, etc.)

        if (numDice === 0 || dieType < 2) {
            return "Comando inválido de rolagem. Tente '1d6', '2d10' ou apenas 'd20'.";
        }
        
        // Limita o número de dados para evitar travar o navegador
        if (numDice > 100) { numDice = 100; } 
        if (dieType > 1000) { return "O valor máximo do dado (Y) é 1000."; }

        let total = 0;
        const results = [];

        for (let i = 0; i < numDice; i++) {
            // Gera um número aleatório entre 1 e dieType (inclusivo)
            const roll = Math.floor(Math.random() * dieType) + 1;
            results.push(roll);
            total += roll;
        }
        
        // VERIFICA O NOME DO USUÁRIO
        const namePrefix = sessionData.userName ? `**${sessionData.userName}** rolou ` : 'Rolagem de dados: '; 
        
        let detailedResults = results.join(' + ');

        if (numDice === 1) {
            // Mensagem simplificada para um único dado
            return `🎲 ${namePrefix} um **d${dieType}** e obteve **${total}**!`;
        } else {
            // Mensagem detalhada para múltiplos dados
            return `🎲 ${namePrefix} **${numDice}d${dieType}**.
Resultados individuais: [${detailedResults}]
Soma total: **${total}**!`;
        }
    }
    // ====================================================================
    
    // --- RECONHECIMENTO DE ENTIDADES SIMULADO ---
    function extractEntities(msg) {
        const entities = {};
        const msgLower = msg.toLowerCase();

        // 1. Extração de Email
        const emailMatch = msg.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/);
        if (emailMatch) {
            entities.email = emailMatch[0];
        }

        // 2. Extração de Pedido
        const orderMatch = msg.match(/(?:#|pedido\s*)\s*(\d{6,})/);
        if (orderMatch) {
            entities.orderId = orderMatch[1];
        }

        // 3. Tentativa de extrair Nome (Lógica mais flexível para AGUARDANDO_NOME)
        if (botState === 'AGUARDANDO_NOME' || botState === 'AGUARDANDO_EMAIL_NOME') {
            const forbiddenWords = ['eu', 'meu', 'e', 'o', 'a', 'um', 'uma', 'sim', 'nao', 'olá', 'oi', 'meu nome é', 'bom dia', 'boa tarde', 'menu', 'reset'];
            
            if (msg.split(' ').length <= 4) { 
                const candidate = msg.split(' ').filter(w => w.length > 0).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                
                const isForbidden = forbiddenWords.some(w => msgLower.trim() === w);

                if (candidate.length > 1 && !isForbidden) {
                     entities.name = candidate.trim();
                }
            }
        }

        return entities;
    }

    // ====================================================================
    // MÓDULO 1: SAUDAÇÕES E INÍCIO DE CONVERSA
    // ====================================================================
    function handleGreetings(msg, entities) {
        const name = sessionData.userName ? sessionData.userName : 'visitante';
        
        if (botState === 'AGUARDANDO_NOME') {
             if (entities.name) {
                 sessionData.userName = entities.name;
                 botState = 'INICIO';
                 return `Que ótimo, ${sessionData.userName}! Eu sou o Atendente IA. Como posso te ajudar hoje?\n1. Ajuda com um Pedido\n2. Trocas e Devoluções\n3. Informações de Produto\n4. Enviar E-mail ao Suporte\n5. Perguntas sobre Programação/IA\n\n*(Dica RPG: Tente digitar '1d20'!)`;
             } else {
                 return "Desculpe, não consegui identificar seu nome. Por favor, digite APENAS seu nome (Ex: Joth) ou digite 'menu' para pular esta etapa.";
             }
        }
        
        if (msg.includes('olá') || msg.includes('oi') || msg.includes('hello') || msg === 'começar' || botState === 'INICIO') {
            if (!sessionData.userName) {
                botState = 'AGUARDANDO_NOME';
                return "Olá! Eu sou seu assistente virtual. Antes de começar, qual é o seu nome?";
            } else {
                botState = 'INICIO'; 
                return `Bem-vindo de volta, ${sessionData.userName}! Qual o seu interesse agora?\n1. Ajuda com um Pedido\n2. Trocas e Devoluções\n3. Informações de Produto\n4. Enviar E-mail ao Suporte\n5. Perguntas sobre Programação/IA\n\n*(Dica RPG: Tente digitar '1d20'!)`;
            }
        }
        return null;
    }

    // ====================================================================
    // MÓDULO 2: ATENDIMENTO GERAL E PEDIDOS (COM FLUXO DE E-MAIL REAL)
    // ====================================================================

    function handlePedidos(msg, entities) {
        const nameGreeting = sessionData.userName ? `${sessionData.userName}, ` : '';
        const msgLower = msg.toLowerCase();

        // --- FLUXO DE FORMULÁRIO DE EMAIL (Opção 4) ---
        if (msg === '4' || msgLower.includes('email') || msgLower.includes('suporte') || msgLower.includes('falar com atendente') || msgLower.includes('humano')) {
            sessionData.emailForm = { nome: sessionData.userName, email: null, mensagem: null }; 
            botState = 'AGUARDANDO_EMAIL_NOME';
            return `${nameGreeting}Certo. Para enviar um e-mail ao nosso suporte, precisamos de 3 informações.\n\n**1/3. Qual é o seu nome COMPLETO?**`;
        }
        
        // 1. Captura o Nome
        if (botState === 'AGUARDANDO_EMAIL_NOME') {
            const nameToUse = entities.name || msg;
            
            if (nameToUse.length >= 3) {
                sessionData.emailForm.nome = nameToUse;
                botState = 'AGUARDANDO_EMAIL_CONTATO';
                return `Ótimo, ${sessionData.emailForm.nome}. **2/3. Qual é o seu MELHOR E-MAIL** para que o suporte possa responder?`;
            } else {
                 return "Por favor, digite seu nome completo para que possamos identificá-lo corretamente.";
            }
        }

        // 2. Captura o E-mail
        if (botState === 'AGUARDANDO_EMAIL_CONTATO') {
            if (entities.email) {
                sessionData.emailForm.email = entities.email;
                botState = 'AGUARDANDO_EMAIL_MENSAGEM';
                return `E-mail salvo: **${sessionData.emailForm.email}**. **3/3. Por favor, descreva sua mensagem ou problema em DETALHES.**`;
            } else {
                return "Por favor, insira um endereço de e-mail válido para que possamos entrar em contato.";
            }
        }

        // 3. Captura a Mensagem e Finaliza (CRIA O LINK MAILTO)
        if (botState === 'AGUARDANDO_EMAIL_MENSAGEM') {
            if (msg.length >= 15) { 
                sessionData.emailForm.mensagem = msg;
                botState = 'INICIO'; 

                // --- CONSTRUÇÃO DO LINK MAILTO ---
                const recipientEmail = 'Nivel1cpv@gmail.com'; 
                const subject = encodeURIComponent(`SUPORTE CHAT: ${msg.substring(0, 50)}...`);
                const bodyContent = `
                    Nome do Cliente: ${sessionData.emailForm.nome}
                    E-mail de Contato: ${sessionData.emailForm.email}
                    --------------------------------------
                    Mensagem Completa:
                    ${sessionData.emailForm.mensagem}
                `;
                const body = encodeURIComponent(bodyContent.trim());
                
                const mailtoLink = `mailto:${recipientEmail}?subject=${subject}&body=${body}`;
                // ----------------------------------------
                
                const finalMessage = `
                    **Obrigado! Sua mensagem está pronta!**
                    
                    Clique no link abaixo para abrir seu cliente de e-mail e enviar a mensagem para a equipe de suporte:
                    
                    <a href="${mailtoLink}" target="_blank" style="color: #df2356; font-weight: bold; text-decoration: underline;">
                        CLIQUE AQUI PARA ENVIAR O E-MAIL
                    </a>
                    
                    Responderemos em até 24 horas úteis no e-mail **${sessionData.emailForm.email}**. Digite 'menu' para voltar.
                `;
                sessionData.emailForm = {}; 
                return finalMessage;
            } else {
                return "Sua mensagem é muito curta. Por favor, descreva seu problema com mais detalhes (mínimo 15 caracteres).";
            }
        }
        // --- FIM FLUXO DE FORMULÁRIO DE EMAIL ---


        // Lógica de Pedidos e Rastreamento (Opção 1)
        if (entities.orderId) {
            botState = 'INICIO';
            return `Obrigado! ${nameGreeting}Verifiquei o pedido **#${entities.orderId}**. Ele foi enviado e deve chegar em 2 dias úteis. Precisa de mais alguma informação?`;
        }

        if (msg === '1' || msgLower.includes('pedido') || msgLower.includes('rastrear')) {
            botState = 'AGUARDANDO_TICKET';
            return `${nameGreeting}Certo. Por favor, digite o **número do seu pedido** ou o **e-mail** cadastrado para que eu possa verificar o status.`;
        } 
        
        // Lógica de Trocas e Devoluções (Opção 2)
        if (msg === '2' || msgLower.includes('troca') || msgLower.includes('devolução')) {
            return `Nossa política permite trocas em até 7 dias. ${nameGreeting}Para iniciar, acesse o link: [Link para Formulário de Devolução]. Posso ajudar com algo mais?`;
        }

        return null;
    }

    // ====================================================================
    // MÓDULO 3: PRODUTOS E ESTOQUE
    // ====================================================================

    function handleProdutos(msg) {
        const nameGreeting = sessionData.userName ? `${sessionData.userName}, ` : '';
        const msgLower = msg.toLowerCase();
        
        if (botState === 'AGUARDANDO_PRODUTO') {
            if (msgLower.includes('moletom')) {
                botState = 'INICIO';
                return `Sim, ${nameGreeting}o **Moletom Abissal** e o **Moletom Coillana** estão disponíveis do P ao XGG. Preço: R$ 149,90. Devo adicionar o 'Abissal' no tamanho 'M' ao seu carrinho?`;
            } else if (msgLower.includes('adesivo')) {
                botState = 'INICIO';
                return "Temos a coleção 'Pixel Art' e a 'Vaporwave' em estoque. Eles custam R$ 8,00 cada. Qual estilo te agrada mais?";
            } else {
                botState = 'INICIO';
                return `Não encontrei um produto exato com '${msg}'. ${nameGreeting}Você pode especificar o nome ou tentar a categoria (Ex: 'caneca', 'camisa')?`;
            }
        }
        
        if (msg === '3' || msgLower.includes('produto') || msgLower.includes('estoque') || msgLower.includes('tamanho')) {
            botState = 'AGUARDANDO_PRODUTO';
            return `${nameGreeting}Qual produto específico você gostaria de saber a disponibilidade, preço ou tamanhos? (Ex: 'Moletom', 'Adesivo', 'CD')`;
        } else if (msgLower.includes('cupon') || msgLower.includes('desconto')) {
            sessionData.lastTopic = 'Cupons';
            return "Temos o cupom **BEMVINDO10** para 10% na primeira compra e **FRETEZERO** para compras acima de R$ 250,00. Qual você gostaria de usar?";
        } else if (msgLower.includes('frete') || msgLower.includes('custo de envio')) {
            return `Nosso frete padrão é R$ 20,00 e o rápido (2-3 dias úteis) é R$ 35,00. ${nameGreeting}O valor exato depende da sua região e é calculado no checkout.`;
        }

        return null;
    }
    
    // ====================================================================
    // MÓDULO 4: TÓPICO BÔNUS (PROGRAMAÇÃO/IA)
    // ====================================================================
    
    const programmingFaqs = [
        { keywords: ['melhor linguagem', 'programação', 'começar'], response: "A melhor linguagem depende do seu objetivo! Para web, comece com **JavaScript/Python**. Para apps móveis, **Kotlin/Swift**. Para ciência de dados, **Python**. Qual área te interessa?" },
        { keywords: ['python', 'usar python'], response: "Python é incrível! É amplamente usado em Data Science, desenvolvimento web (com frameworks como Django e Flask) e automação. É conhecido por sua sintaxe limpa e legível." },
        { keywords: ['javascript', 'usar javascript'], response: "JavaScript é essencial para desenvolvimento web (frontend e backend com Node.js). Praticamente todos os sites e aplicações modernas o utilizam. É a linguagem mais versátil da web." },
        { keywords: ['o que é ia', 'inteligencia artificial'], response: "IA (Inteligência Artificial) é um campo da ciência da computação dedicado a criar sistemas que podem realizar tarefas que normalmente exigiriam inteligência humana, como aprendizado, percepção e tomada de decisão. Eu sou um exemplo simples disso!" },
    ];

    function handleProgramming(msg) {
        const msgLower = msg.toLowerCase();
        
        if (msg === '5' || msgLower.includes('programação') || msgLower.includes('ia') || msgLower.includes('linguagem')) {
            sessionData.lastTopic = 'Programação';
            botState = 'TOPICO_PROGRAMACAO';
            return "Interessante! Posso falar sobre linguagens, IA, frameworks e a carreira de dev. Qual o seu nível: Iniciante, Intermediário ou Experiente?";
        }
        
        if (botState === 'TOPICO_PROGRAMACAO') {
            
            for (const faq of programmingFaqs) {
                if (faq.keywords.some(keyword => msgLower.includes(keyword))) {
                    sessionData.lastQuestion = faq.keywords[0];
                    return faq.response + "\n\nQuer que eu me aprofunde mais neste tema ou prefere mudar de assunto?";
                }
            }

            if (msgLower.includes('aprofundar') || msgLower.includes('mais')) {
                 if (sessionData.lastQuestion === 'ia' || sessionData.lastQuestion === 'o que é ia') {
                     return "O aprofundamento em IA envolve Redes Neurais e Deep Learning. Você conhece o conceito de 'backpropagation'? Sim ou Não?";
                 } else {
                     return "Para aprofundar, você pode me perguntar sobre 'frameworks avançados', 'performance' ou 'carreira'.";
                 }
            }

            if (msgLower.includes('iniciante') || msgLower.includes('intermediário') || msgLower.includes('experiente')) {
                sessionData.level = msgLower;
                return `Ótimo, ${sessionData.userName}! Com seu nível **${msgLower}**, eu recomendaria focar em **um projeto prático**. Quer uma dica de projeto?`;
            } else if (msgLower.includes('projeto') || msgLower.includes('dica')) {
                 return "Para iniciantes: crie um quiz interativo simples com JavaScript. Para intermediários: desenvolva uma API REST com Python/Node.js. Qual você prefere?";
            }
            
            return "Ainda estou no tópico de Programação. Tente perguntar sobre 'Git', 'HTML/CSS', ou 'Cloud'!";
        }
        
        return null;
    }
    
    // ====================================================================
    // FUNÇÃO PRINCIPAL: Processar Envio
    // ====================================================================

    function getBotResponse(userMessage) {
        const msg = userMessage.toLowerCase().trim();
        const entities = extractEntities(userMessage); 

        // 1. Comando de saída/reset sempre funciona
        if (msg === 'voltar' || msg === 'menu' || msg === 'reset') {
            botState = 'INICIO';
            sessionData.lastTopic = null;
            sessionData.emailForm = {}; 
            return handleGreetings('olá', {});
        }
        
        // 2. TENTA A ROLAGEM DE DADOS (Prioridade alta para o RPG)
        const diceResult = rollDice(userMessage);
        if (diceResult) {
            // Se rolou dados, reseta o estado para INICIO (para não interferir nos formulários)
            if (botState !== 'INICIO' && !botState.startsWith('TOPICO')) {
                botState = 'INICIO'; 
            }
            return diceResult;
        }

        // --- 3. EASTER EGG: AURA (NOVO) ---
        if (msg.includes('aura')) {
            return "+ EGO PORRA LABUBU PSITACHES DA SILVA CARALHOOOOOOOOOOOOOOOO";
        }

        // --- 4. EASTER EGG: PERSONA ---
        if (msg.includes('persona')) {
            const gifUrl = "https://i.pinimg.com/originals/f3/01/3e/f3013e356a3829c077c84c321798982f.gif";
            return `É O PERSONA 3 CARALHOOOOOOOOOOO<br><img src="${gifUrl}" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">`;
        }
        
        // 5. Tenta responder os fluxos de Captura de Nome e E-mail
        if (botState === 'AGUARDANDO_NOME' || botState.startsWith('AGUARDANDO_EMAIL')) {
            return handlePedidos(msg, entities) || handleGreetings(msg, entities) || "Por favor, siga as instruções ou digite 'menu' para cancelar.";
        }
        
        // 6. Tenta responder por estados/tópicos
        let response = handlePedidos(msg, entities);
        if (response) return response;
        
        response = handleProdutos(msg);
        if (response) return response;
        
        response = handleProgramming(msg);
        if (response) return response;
        
        // 7. Tenta responder por saudações/início
        response = handleGreetings(msg, entities);
        if (response) return response;

        // 8. Último recurso (Fallback padrão)
        const name = sessionData.userName ? sessionData.userName : '';
        return `Desculpe ${name}, não entendi. Digite 'menu' para ver as opções principais ou tente digitar uma palavra-chave como 'rastrear' ou 'desconto'.`;
    }


    // --- FUNÇÃO PRINCIPAL DE ENVIO ---
    function handleSendMessage() {
        const userMessage = userInput.value.trim();

        if (userMessage !== "") {
            addMessage(userMessage, 'user');
            userInput.value = ""; 
            userInput.disabled = true;
            sendBtn.disabled = true;
            
            setTimeout(() => {
                const botResponse = getBotResponse(userMessage);
                addMessage(botResponse, 'bot');
                userInput.disabled = false;
                sendBtn.disabled = false;
                userInput.focus();
            }, 1000); 
        }
    }

    // --- LÓGICA DE EVENTOS E VOZ (MANTIDA) ---
    sendBtn.addEventListener('click', handleSendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSendMessage();
        }
    });

    if (micBtn) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
             micBtn.style.display = 'none'; 
        } else {
            const recognition = new SpeechRecognition();
            recognition.lang = 'pt-BR'; 
            recognition.continuous = false; 
            recognition.interimResults = false;

            micBtn.addEventListener('click', () => {
                 userInput.value = ''; 
                 micBtn.style.color = 'red'; 
                 recognition.start();
            });
            recognition.addEventListener('result', (event) => {
                 const speechResult = event.results[0][0].transcript;
                 userInput.value = speechResult;
            });
            recognition.addEventListener('end', () => {
                 micBtn.style.color = '#df2356';
                 if (userInput.value.trim() !== '') {
                     handleSendMessage(); 
                 }
            });
            recognition.addEventListener('error', (event) => {
                 console.error('Erro no reconhecimento de fala:', event.error);
                 micBtn.style.color = '#df2356';
            });
        }
    }
    
    // Inicia a conversa com uma saudação para capturar o nome
    setTimeout(() => {
        addMessage(handleGreetings('olá', {}), 'bot');
    }, 100);
});