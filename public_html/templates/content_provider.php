<?php
// templates/content_provider.php
// Provides professional text for OpenBorme static pages

function get_page_content($path, $extra = [])
{
    $user_name = "Pablo Cirre Gómez";
    $user_address = "Emperatriz Eugenia n8 4a, 18002 Granada";

    $content = [
        'personas' => <<<HTML
            <div class="v3-status-box">
                <div class="v3-status-icon">🔒</div>
                <h2 style="margin-bottom: var(--space-4); color: var(--brand-dark);">Funcionalidad en Pausa por Privacidad</h2>
                <p style="max-width: 650px; color: var(--text-muted); font-size: 1.15rem; line-height: 1.6;">
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
            <h2 style="margin-bottom: var(--space-4);">Política de Privacidad y Cumplimiento Normativo</h2>
            <p>OpenBorme se compromete con la transparencia y el respeto a la privacidad individual, equilibrando el derecho a la información pública con la protección de datos personales.</p>

            <h3 style="margin-top: var(--space-6);">1. Naturaleza de los Datos</h3>
            <p>Los datos mostrados en este portal provienen exclusivamente de fuentes de acceso público: el <strong>Boletín Oficial del Registro Mercantil (BORME)</strong>.</p>
            <ul>
                <li><strong>Base Legal:</strong> El tratamiento se basa en la normativa de reutilización de información del sector público (Ley 37/2007) y el interés legítimo de transparencia empresarial.</li>
                <li><strong>Finalidad:</strong> Facilitar la consulta de actos mercantiles y la seguridad del tráfico jurídico.</li>
            </ul>

            <h3 style="margin-top: var(--space-6);">2. No Indexación de Personas Físicas</h3>
            <p>Por defecto, OpenBorme <strong>NO permite la búsqueda por nombre de persona física</strong> en la interfaz pública para evitar la creación de perfiles no solicitados.</p>
            <ul>
                <li>Las búsquedas están orientadas a <strong>Empresas</strong> (personas jurídicas).</li>
                <li>Los nombres de administradores o apoderados solo aparecen vinculados dentro de la ficha de la empresa correspondiente.</li>
            </ul>

            <h3 style="margin-top: var(--space-6);">3. Derechos ARCO y "Takedown"</h3>
            <div class="v3-highlight-box">
                <p style="font-weight: 600; color: var(--brand-dark); margin-bottom: var(--space-2);">Si usted es un particular y desea que su nombre no aparezca indexado en OpenBorme:</p>
                <ol style="margin-bottom: var(--space-4);">
                    <li>Envíe un email a <a href="mailto:privacidad@openborme.es" style="color: var(--accent); font-weight: 600;">privacidad@openborme.es</a>.</li>
                    <li>Indique la URL exacta o el acto donde aparecen sus datos.</li>
                    <li>Procederemos a <strong>anonimizar</strong> su nombre en nuestra base de datos derivada.</li>
                </ol>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0;">Nota: Esto no elimina el dato del BORME oficial, solo de nuestra visualización técnica.</p>
            </div>

            <h3 style="margin-top: var(--space-6);">4. Limitación de Responsabilidad</h3>
            <p>OpenBorme ofrece los datos "tal cual" (as-is). No garantizamos la exactitud completa debido a posibles errores en el proceso de OCR automatizado. Para fines legales, consulte siempre el PDF oficial firmado digitalmente por la Agencia BOE.</p>
            HTML
        ,
        'metodologia' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Metodología de Extracción</h2>
            <p>OpenBorme utiliza un pipeline de procesamiento avanzado para transformar los boletines oficiales en datos útiles y legibles.</p>

            <h3 style="margin-top: var(--space-6);">1. Ingesta de Datos</h3>
            <p>Cada día, nuestro sistema se conecta a la API de datos abiertos de la Agencia Estatal BOE para obtener el sumario del Boletín Oficial del Registro Mercantil. Descargamos tanto los sumarios en XML como los documentos individuales en PDF y XML (Secciones I y II).</p>

            <h3 style="margin-top: var(--space-6);">2. Parsing y Estructuración</h3>
            <div class="card" style="margin: var(--space-4) 0;">
                <ul style="margin-bottom: 0;">
                    <li style="margin-bottom: 0.5rem;"><strong>Motor PDF (Sección I):</strong> Extrae texto plano de los boletines provinciales y utiliza expresiones regulares para identificar actos inscritos, empresas y CIFs.</li>
                    <li><strong>Motor XML (Sección II):</strong> Procesa los anuncios legales estructurados directamente desde la fuente XML oficial.</li>
                </ul>
            </div>

            <h3 style="margin-top: var(--space-6);">3. Normalización</h3>
            <p>Los datos extraídos se normalizan para corregir errores comunes en la fuente original (como formatos de CIF inconsistentes) y se preparan para su indexación en nuestra base de datos derivada.</p>

            <div class="v3-note-box">
                <strong style="color: var(--brand-dark); display: block; margin-bottom: 4px;">Nota de Trazabilidad</strong>
                <span style="color: var(--text-muted);">Todo este proceso se realiza de forma automatizada y con trazabilidad completa a los documentos originales del BOE a través de hashes MD5 verificables.</span>
            </div>
            HTML
        ,
        'fuentes' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Fuentes de Información Oficial</h2>
            <p>La transparencia y la trazabilidad son los pilares de OpenBorme. Todos nuestros datos provienen de fuentes oficiales públicas.</p>

            <h3 style="margin-top: var(--space-6);">Fuente Oficial</h3>
            <p>Los datos publicados en este portal han sido obtenidos de la <strong>Agencia Estatal Boletín Oficial del Estado (BOE)</strong>, específicamente del Boletín Oficial del Registro Mercantil (BORME).</p>
            <p>Puede consultar la fuente original en: <a href="https://www.boe.es/diario_borme/" target="_blank">www.boe.es/diario_borme/</a></p>

            <div class="v3-legal-warning">
                <h4 style="color: var(--text-main); font-weight: 800; margin-bottom: var(--space-2); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.05em;">⚖️ Aviso de No Oficialidad</h4>
                <p style="color: var(--text-muted); line-height: 1.5;"><strong>OpenBorme no es el Registro Mercantil</strong> ni tiene carácter de fuente oficial. Somos una plataforma de ingeniería de datos que reestructura la información oficial para fines de análisis y consulta técnica.</p>
                <p style="margin-top: var(--space-4); font-size: 0.85rem; color: var(--text-secondary);">Para trámites legales, administrativos o mercantiles con validez jurídica, debe acudirse siempre a la fuente oficial (BOE o Registro Mercantil correspondiente).</p>
            </div>

            <h3 style="margin-top: var(--space-6);">Reutilización de Información</h3>
            <p>Este proyecto se acoge a la normativa de reutilización de información del sector público (Ley 37/2007). Los datos son procesados sin desnaturalizar su contenido original.</p>
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
            <div class="v3-status-box">
                <h2 style="margin-bottom: var(--space-3); color: var(--brand-dark);">Soporte y Consultas</h2>
                <p style="margin-bottom: var(--space-6); color: var(--text-muted); max-width: 600px; font-size: 1.1rem;">Para cuestiones técnicas, soporte comercial o solicitudes de rectificación de datos, contacte con nuestro equipo.</p>
                <div style="display: flex; gap: var(--space-3); justify-content: flex-start; flex-wrap: wrap;">
                    <a href="mailto:admin@openborme.es" class="btn btn-primary btn-m" style="background: var(--brand-dark); border-radius: var(--radius-md);">ENVIAR EMAIL</a>
                    <a href="https://www.linkedin.com/in/pablocirre" class="btn btn-secondary btn-m" style="border-radius: var(--radius-md);">PERFIL LINKEDIN</a>
                </div>
                <p style="margin-top: var(--space-6); font-size: 0.85rem; color: var(--text-secondary);">Para consultas legales formales, consulte nuestro <a href="/aviso-legal" style="color: var(--accent); font-weight: 600;">Aviso Legal</a>.</p>
            </div>
            HTML
        ,
        'calidad-de-datos' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Métricas de Calidad y Transparencia Técnica</h2>
            <p>En OpenBorme, la integridad del dato es crítica. Monitorizamos continuamente la calidad de nuestro proceso de extracción automatizado.</p>

            <h3 style="margin-top: var(--space-6);">1. Trazabilidad del Dato</h3>
            <p>Cada registro en nuestra base de datos mantiene un vínculo inmutable con su fuente:</p>
            <ul>
                <li><strong>Hash MD5:</strong> Cada acto extraído genera un hash único basado en su contenido.</li>
                <li><strong>Enlace al Original:</strong> Referencia directa a la URL del PDF/XML en <code>boe.es</code>.</li>
            </ul>

            <h3 style="margin-top: var(--space-6);">2. Precisión del OCR</h3>
            <p>Utilizamos técnicas de extracción híbridas:</p>
            <ul>
                <li><strong>Nivel 1 (XML Estructurado):</strong> Precisión del <strong>100%</strong>. Se usa para la Sección II y sumarios.</li>
                <li><strong>Nivel 2 (PDF Textual):</strong> Precisión estimada del <strong>99.5%</strong>. Los errores suelen ser caracteres especiales o codificaciones extrañas en los PDFs originales.</li>
            </ul>

            <h3 style="margin-top: var(--space-6);">3. Gestión de Erratas</h3>
            <p>El sistema detecta automáticamente patrones de "Fe de Erratas" publicados en el BORME. Cuando una corrección es publicada, el sistema intenta vincularla con el acto original marcándolo como <code>Corregido</code>.</p>
            HTML
        ,
        'modelo-de-datos' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Modelo de Datos</h2>
            <p>OpenBorme utiliza un modelo relacional simplificado diseñado para la búsqueda rápida y el análisis de series temporales.</p>

            <div class="results-layout" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-4);">
                <div class="card">
                    <h3 style="margin-bottom: var(--space-3);">🏢 Empresa (Company)</h3>
                    <p>Representa la entidad legal inscrita.</p>
                    <ul style="font-size: 0.95em;">
                        <li><strong>CIF:</strong> Identificador fiscal único (PK).</li>
                        <li><strong>Nombre:</strong> Razón social normalizada.</li>
                        <li><strong>Provincia:</strong> Delegación del Registro Mercantil.</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom: var(--space-3);">📜 Acto (Act)</h3>
                    <p>Representa un evento atómico publicado.</p>
                    <ul style="font-size: 0.95em;">
                        <li><strong>ID:</strong> Hash MD5 de trazabilidad.</li>
                        <li><strong>Tipo:</strong> Categoría normalizada (ej: Constitución).</li>
                        <li><strong>Fecha:</strong> Publicación en BOE (YYYY-MM-DD).</li>
                        <li><strong>Detalles:</strong> Texto completo extraído (OCR).</li>
                    </ul>
                </div>
            </div>

            <h3 style="margin-top: var(--space-6);">Formatos de Exportación</h3>
            <p>Los datos están disponibles para descarga masiva en <strong>CSV</strong> (Excel/Pandas) y <strong>JSON</strong> (Integración).</p>
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
HTML,
        'manifiesto' => <<<HTML
            <h2 style="margin-bottom: var(--space-4);">Manifiesto Técnico: Estándares de Transparencia</h2>
            <p>Este documento establece los principios técnicos y éticos que rigen el proyecto OpenBorme. Su objetivo es garantizar la reproducibilidad, la confianza en los datos estructurados y la claridad en la gestión del proyecto.</p>

            <h3 style="margin-top: var(--space-6);">1. Contrato de Datos (Data Contract)</h3>
            <p>OpenBorme se compromete a mantener un esquema de datos estable y versionado para facilitar la interoperabilidad.</p>
            <div class="card" style="margin: var(--space-4) 0;">
                <h4>Entidad: Acto (Publication)</h4>
                <p>Unidad atómica de información registral.</p>
                <ul>
                    <li><strong>id:</strong> Identificador único (BORME-A-YYYY-...).</li>
                    <li><strong>hash:</strong> Integridad MD5 del texto extraído.</li>
                    <li><strong>raw_text:</strong> Texto original verificado.</li>
                </ul>
            </div>

            <h3 style="margin-top: var(--space-6);">2. Metodología Reproducible</h3>
            <ol>
                <li><strong>Ingesta:</strong> Descarga diaria de XMLs y PDFs de la API oficial.</li>
                <li><strong>Extracción:</strong> Procesamiento OCR y segmentación por regex.</li>
                <li><strong>Normalización:</strong> Limpieza y estandarización de entidades.</li>
                <li><strong>QA Automático:</strong> Validación cruzada contra sumario XML.</li>
            </ol>

            <h3 style="margin-top: var(--space-6);">3. Código Abierto</h3>
            <p>Los módulos de ingesta, extracción y normalización son totalmente auditables en nuestro repositorio público. La infraestructura crítica se mantiene privada por seguridad.</p>
            HTML
        ,
        'objetivos' => <<<HTML
             <h2 style="margin-bottom: var(--space-4);">Objetivos y Hoja de Ruta</h2>
             <p>OpenBorme nace para solucionar las carencias de accesibilidad del sistema actual, transformando PDFs estáticos en datos vivos.</p>

             <h3 style="margin-top: var(--space-6);">Arquitectura "Fly Mode" (Zero-Storage)</h3>
             <p>Hemos implementado una arquitectura de procesamiento efímero:</p>
             <ul>
                <li><strong>Procesamiento al Vuelo:</strong> Descarga, extracción y borrado inmediato del PDF original.</li>
                <li><strong>Privacidad por Diseño:</strong> Minimización de datos almacenados localmente.</li>
                <li><strong>Eficiencia:</strong> Eliminación de la necesidad de terabytes de almacenamiento.</li>
             </ul>

             <h3 style="margin-top: var(--space-6);">El Estándar "Borme Perfecto"</h3>
             <p>Nuestra meta es cumplir con los 10 puntos de excelencia:</p>
             <ul class="checklist" style="list-style: none;">
                <li>✅ Trazabilidad absoluta a la fuente oficial.</li>
                <li>✅ Buscador unificado (Sección I y II).</li>
                <li>✅ Ficha de empresa con timeline de eventos.</li>
                <li>✅ API con endpoints por entidad.</li>
                <li>⏳ Dumps masivos en Parquet/JSONL.</li>
             </ul>
             HTML
        ,
    ];

    return $content[$path] ?? "
        <div style='padding: 3rem; border: 1px dashed #ccc; text-align: center;'>
            <p style='color: #666;'>Esta sección (<strong>/$path</strong>) está siendo poblada con contenidos definitivos.</p>
            <p>Para más información, consulte nuestra documentación técnica o contacte con soporte técnico.</p>
        </div>";
}
