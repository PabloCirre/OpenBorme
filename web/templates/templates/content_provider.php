<?php
// templates/content_provider.php
// Provides professional text for OpenBorme static pages

function get_page_content($path, $extra = [])
{
    $user_name = "Pablo Cirre Gómez";
    $user_address = "Emperatriz Eugenia n8 4a, 18002 Granada";

    $content = [
        'personas' => <<<HTML
            <div style="background: #f9fafb; border: 1px solid var(--border-dark); border-radius: var(--radius-lg); padding: 4rem; text-align: center;">
                <div style="font-size: 48px; margin-bottom: var(--space-4);">🔒</div>
                <h2 style="margin-bottom: var(--space-4);">Funcionalidad en Pausa por Privacidad</h2>
                <p style="max-width: 600px; margin: 0 auto; color: var(--text-secondary); line-height: 1.6;">
                    El buscador de personas físicas y cargos ha sido temporalmente desactivado para asegurar el cumplimiento estricto 
                    del Reglamento General de Protección de Datos (RGPD) y garantizar la privacidad individual.
                </p>
                <div style="margin-top: var(--space-6);">
                    <a href="/privacidad" class="btn btn-secondary">Leer Política de Privacidad</a>
                </div>
            </div>
            HTML
        ,
        'aviso-legal' => <<<HTML
            <div style="margin-bottom: var(--space-6);">
                <p>En cumplimiento del artículo 10 de la Ley 34/2002, de 11 de julio, de Servicios de la Sociedad de la Información y Comercio Electrónico (LSSICE), se exponen los siguientes datos identificativos del responsable técnico:</p>
                <div class="card" style="margin-top: var(--space-4);">
                    <ul style="list-style: none;">
                        <li><strong>Titular Responsable:</strong> $user_name</li>
                        <li><strong>Domicilio Técnico:</strong> $user_address</li>
                        <li><strong>Canal de Comunicaciones:</strong> contacto@openborme.es</li>
                        <li><strong>Finalidad del Proyecto:</strong> Reutilización y estructuración técnica de información pública del Registro Mercantil.</li>
                    </ul>
                </div>
            </div>
            <h2>1. Condiciones Generales de Uso</h2>
            <p>El acceso a este portal técnico es de carácter abierto y atribuye la condición de USUARIO. El portal proporciona acceso a informaciones y datos estructurados pertenecientes a OpenBorme derivados de fuentes oficiales.</p>
            HTML
        ,
        'terminos-de-uso' => <<<HTML
            <h2>Condiciones Generales</h2>
            <p>El uso de OpenBorme implica la aceptación de estas condiciones. El usuario se compromete a hacer un uso lícito de los contenidos, evitando cualquier acción que pueda dañar la integridad de los datos o la disponibilidad del servicio.</p>
            <p>Queda prohibido el uso de técnicas de hacking o scraping agresivo que superen los límites razonables de consulta personal o profesional.</p>
HTML
        ,
        'privacidad' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Protección de Datos (RGPD)</h2>
            <p style="margin-bottom: var(--space-4);"><strong>Responsable del Tratamiento:</strong> $user_name. <br><strong>Finalidad:</strong> Gestión de consultas técnicas y alertas registrales. <br><strong>Legitimación:</strong> Consentimiento explícito del interesado y cumplimiento de obligaciones legales de transparencia.</p>
            
            <p style="margin-bottom: var(--space-4);">Para el ejercicio de sus derechos de acceso, rectificación, portabilidad o supresión de datos personales, puede dirigir una comunicación formal a la dirección técnica: <code class="mono">$user_address</code> o vía electrónica a través de los canales habilitados.</p>
            
            <div class="trazabilidad">
                <h5>Compromiso de Privacidad</h5>
                <p>OpenBorme prioriza la privacidad individual, limitando la visibilidad de datos de personas físicas y centrando su actividad en la estructuración de eventos societarios y mercantiles.</p>
            </div>
            HTML
        ,
        'metodologia' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Estructuración y Procesamiento de Datos</h2>
            <p style="margin-bottom: var(--space-4);">OpenBorme utiliza una infraestructura híbrida de procesamiento para transformar las publicaciones diarias del BOE en datos legibles por máquinas:</p>
            
            <div class="results-layout" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="card">
                    <h4 style="margin-bottom: var(--space-2);">1. Ingesta</h4>
                    <p style="font-size: 14px;">Descarga diaria de sumarios en XML y actos en PDF mediante APIs de datos abiertos.</p>
                </div>
                <div class="card">
                    <h4 style="margin-bottom: var(--space-2);">2. Extracción</h4>
                    <p style="font-size: 14px;">Uso de técnicas de visión y procesamiento de lenguaje natural (NLP) para identificar entidades y actos.</p>
                </div>
                <div class="card">
                    <h4 style="margin-bottom: var(--space-2);">3. Normalización</h4>
                    <p style="font-size: 14px;">Validación de identificadores (CIF), fechas y tipos de actos según el estándar mercantil.</p>
                </div>
            </div>
            HTML
        ,
        'cookies' => <<<HTML
            <p>OpenBorme utiliza cookies técnicas para el funcionamiento de la web. No utilizamos cookies de rastreo publicitario de terceros sin su consentimiento previo.</p>
HTML
        ,
        'exencion-responsabilidad' => <<<HTML
            <div style="background: #fff5f5; border: 2px solid var(--boe-red); padding: 2rem; border-radius: 4px;">
                <p style="color: var(--boe-red); font-weight: 700; font-size: 1.2rem;">ADVERTENCIA LEGAL IMPORTANTE</p>
                <p>OpenBorme es un proyecto independiente y <strong>NO tiene vinculación oficial</strong> con el Registro Mercantil ni con la Agencia Estatal BOE.</p>
                <p>La información mostrada es una <strong>reutilización</strong> de datos públicos y puede contener errores derivados del proceso automatizado de extracción. Para actos con relevancia jurídica, consulte siempre la fuente oficial.</p>
            </div>
HTML
        ,
        'faq' => <<<HTML
            <div class="faq-item" style="margin-bottom: 2rem;">
                <h3>¿Qué es OpenBorme?</h3>
                <p>Es una plataforma de datos abiertos que estructura el BORME para facilitar su consulta profesional y por IAs.</p>
            </div>
            <div class="faq-item" style="margin-bottom: 2rem;">
                <h3>¿Con qué frecuencia se actualiza?</h3>
                <p>Diariamente, sincronizando con las publicaciones oficiales del BOE de lunes a viernes.</p>
            </div>
HTML
        ,
        'status' => <<<HTML
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 2rem;">
                <div style="width: 15px; height: 15px; background: #00ca00; border-radius: 50%;"></div>
                <span style="font-weight: 700;">Sistemas Operativos</span>
            </div>
            <p>Todos los servicios de extracción y búsqueda están funcionando con normalidad.</p>
HTML
        ,
        'contacto' => <<<HTML
            <div class="card" style="text-align: center; padding: 4rem 2rem;">
                <h2 style="margin-bottom: var(--space-3);">Soporte y Consultas</h2>
                <p style="margin-bottom: var(--space-6); color: var(--text-secondary);">Para cuestiones técnicas, soporte comercial o solicitudes de rectificación de datos, contacte con nuestro equipo.</p>
                <div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
                    <a href="mailto:admin@openborme.es" class="btn btn-primary">ENVIAR EMAIL</a>
                    <a href="https://www.linkedin.com/in/pablocirre" class="btn btn-secondary">PERFIL LINKEDIN</a>
                </div>
                <p style="margin-top: var(--space-5); font-size: 13px; color: var(--text-muted);">Para consultas legales formales, consulte nuestro <a href="/aviso-legal">Aviso Legal</a>.</p>
            </div>
            HTML
        ,
        'calidad-de-datos' => <<<HTML
            <p>La calidad de nuestros datos es una prioridad. Realizamos auditorías semanales para comparar el volumen de registros extraídos con los publicados oficialmente en el BOE.</p>
            <ul>
                <li><strong>Cobertura:</strong> 100% de los boletines diarios.</li>
                <li><strong>Precisión CIF:</strong> >98% en Sección I.</li>
            </ul>
HTML
        ,
        'reutilizacion-y-atribucion' => <<<HTML
            <p>Este portal se acoge a la Ley 37/2007 sobre reutilización de la información del sector público. Se permite la reutilización de los datos aquí contenidos siempre que se cite la fuente original (BOE) y el origen de los datos estructurados (OpenBorme).</p>
HTML
        ,
        'canal-de-rectificacion' => <<<HTML
            <p>Si detecta algún error en los datos de su empresa o desea solicitar la retirada de información bajo el amparo de la normativa vigente, puede enviar un correo a rectificacion@openborme.es adjuntando la documentación justificativa.</p>
HTML
        ,
        'proteccion-de-datos/derechos' => <<<HTML
            <h2>Ejercicio de Derechos</h2>
            <p>Usted puede ejercer sus derechos ARCO (Acceso, Rectificación, Cancelación y Oposición) enviando una solicitud a $user_name en la dirección $user_address.</p>
HTML
        ,
        'quienes-somos' => <<<HTML
            <p>OpenBorme nace de la necesidad de modernizar el acceso a la información empresarial pública en España. Somos un equipo liderado por $user_name enfocado en la transparencia y los datos abiertos.</p>
HTML
        ,
        'seguridad' => <<<HTML
            <p>Cumplimos con el Esquema Nacional de Seguridad (ENS) en sus niveles básicos, garantizando la integridad de los datos extraídos y la seguridad de las conexiones SSL en todo el dominio.</p>
HTML
        ,
        'prensa' => <<<HTML
            <p>Para notas de prensa o solicitudes de información periodística basada en datos masivos del BORME, contacte con prensa@openborme.es.</p>
HTML
        ,
        'alertas' => <<<HTML
            <p>Configure alertas personalizadas para recibir un correo cada vez que una empresa o CIF de su interés aparezca publicado en el BORME. Servicio próximamente disponible.</p>
HTML
        ,
        'mi-cuenta/acceso' => <<<HTML
            <div style="max-width: 400px; margin: 0 auto; padding: 2rem; border: 1px solid var(--border-color);">
                <h3 style="text-align: center; margin-bottom: 2rem;">Acceso de Usuario</h3>
                <input type="text" placeholder="Email" style="width: 100%; margin-bottom: 1rem; padding: 0.5rem;">
                <input type="password" placeholder="Contraseña" style="width: 100%; margin-bottom: 1rem; padding: 0.5rem;">
                <button style="width: 100%; background: var(--boe-red); color: white; padding: 0.5rem; border: none;">ENTRAR</button>
            </div>
HTML
    ];

    return $content[$path] ?? "
        <div style='padding: 3rem; border: 1px dashed #ccc; text-align: center;'>
            <p style='color: #666;'>Esta sección (<strong>/$path</strong>) está siendo poblada con contenidos definitivos.</p>
            <p>Para más información, consulte nuestra documentación técnica o contacte con soporte técnico.</p>
        </div>";
}
