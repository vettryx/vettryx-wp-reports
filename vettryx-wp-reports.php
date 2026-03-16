<?php
/**
 * Plugin Name: VETTRYX WP Reports
 * Plugin URI:  https://github.com/vettryx/vettryx-wp-core
 * Description: Submódulo do VETTRYX WP Core para geração nativa de relatórios mensais de manutenção e controle de SLA.
 * Version:     1.0.1
 * Author:      VETTRYX Tech
 * Author URI:  https://vettryx.com.br
 * License:     Proprietária (Uso Comercial Exclusivo)
 * Vettryx Icon: dashicons-media-document
 */

// Segurança: Impede o acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ==============================================================================
 * 1. REGISTRO DE ROTAS E MENU
 * Responsável por adicionar o módulo no ecossistema VETTRYX Core.
 * ==============================================================================
 */

add_action('admin_menu', 'vettryx_reports_add_submenu', 99);
function vettryx_reports_add_submenu() {
    add_submenu_page(
        'vettryx-core-modules',
        'VETTRYX WP Reports',
        'VETTRYX WP Reports',
        'manage_options',
        'vettryx-wp-reports',
        'vettryx_reports_dashboard_html'
    );
}

/**
 * ==============================================================================
 * 2. INTERFACE E LÓGICA DO DASHBOARD
 * Lida com o formulário, salva os dados temporários e renderiza o preview.
 * ==============================================================================
 */

function vettryx_reports_dashboard_html() {
    if (!current_user_can('manage_options')) return;

    // Processa o salvamento/geração do formulário
    if (isset($_POST['vettryx_report_action']) && check_admin_referer('vettryx_report_nonce')) {
        $report_data = [
            'month_year'   => sanitize_text_field($_POST['report_month_year']),
            'hours_used'   => sanitize_text_field($_POST['report_hours']),
            'updates_done' => isset($_POST['report_updates']) ? 'Sim' : 'Não',
            'backups_done' => isset($_POST['report_backups']) ? 'Sim' : 'Não',
            'security_ok'  => isset($_POST['report_security']) ? 'Sim' : 'Não',
            'tasks_desc'   => sanitize_textarea_field($_POST['report_tasks'])
        ];
        
        // Salva os dados no banco para o preview (sobrescreve o anterior)
        update_option('vettryx_latest_report_data', $report_data);
        echo '<div class="notice notice-success is-dismissible"><p>Dados do relatório atualizados. Clique em Salvar como PDF para gerar o documento.</p></div>';
    }

    // Puxa os dados salvos ou valores em branco para inicializar
    $data = get_option('vettryx_latest_report_data', [
        'month_year'   => wp_date('F / Y'),
        'hours_used'   => '05:00',
        'updates_done' => 'Sim',
        'backups_done' => 'Sim',
        'security_ok'  => 'Sim',
        'tasks_desc'   => "1. Monitoramento preventivo realizado.\n2. Verificação de integridade do banco de dados."
    ]);
    ?>
    <div class="wrap vettryx-no-print">
        <h1 style="display:flex; align-items:center; gap:10px; margin-bottom: 20px;">
            <span class="dashicons dashicons-media-document" style="font-size: 28px; width: 28px; height: 28px;"></span> 
            VETTRYX WP Reports
        </h1>
        <p>Gere os relatórios de manutenção mensal para enviar aos clientes, em conformidade com o escopo de serviços.</p>

        <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; max-width: 800px; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 30px;">
            <form method="post" action="">
                <?php wp_nonce_field('vettryx_report_nonce'); ?>
                <input type="hidden" name="vettryx_report_action" value="generate">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="report_month_year">Mês/Ano de Referência</label></th>
                        <td><input type="text" name="report_month_year" id="report_month_year" value="<?php echo esc_attr($data['month_year']); ?>" class="regular-text" placeholder="Ex: Março / 2026"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="report_hours">Horas Consumidas (SLA)</label></th>
                        <td>
                            <input type="text" name="report_hours" id="report_hours" value="<?php echo esc_attr($data['hours_used']); ?>" class="small-text" placeholder="05:00">
                            <span class="description">Tempo gasto nas demandas de suporte do mês.</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Checklist de Manutenção</th>
                        <td>
                            <label style="display:block; margin-bottom: 5px;">
                                <input type="checkbox" name="report_updates" value="1" <?php checked($data['updates_done'], 'Sim'); ?> /> Atualização de Core, Temas e Plugins
                            </label>
                            <label style="display:block; margin-bottom: 5px;">
                                <input type="checkbox" name="report_backups" value="1" <?php checked($data['backups_done'], 'Sim'); ?> /> Verificação de Integridade de Backups
                            </label>
                            <label style="display:block;">
                                <input type="checkbox" name="report_security" value="1" <?php checked($data['security_ok'], 'Sim'); ?> /> Monitoramento de Uptime e Segurança
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="report_tasks">Descritivo de Tarefas</label></th>
                        <td>
                            <textarea name="report_tasks" id="report_tasks" rows="5" style="width:100%;" placeholder="Descreva os ajustes, edições de página e correções de bugs..."><?php echo esc_textarea($data['tasks_desc']); ?></textarea>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">Atualizar Dados do Relatório</button>
                    <button type="button" class="button button-secondary button-large" onclick="window.print();" style="margin-left: 10px;">
                        <span class="dashicons dashicons-pdf" style="margin-top: 3px;"></span> Salvar como PDF
                    </button>
                </p>
            </form>
        </div>
    </div>

    <?php
    /**
     * ==============================================================================
     * 3. TEMPLATE DE IMPRESSÃO (O PDF GERADO)
     * Utiliza CSS @media print para ocultar o painel WP e exibir apenas isso.
     * ==============================================================================
     */
    ?>
    <style>
        /* Esconde a visualização do relatório no uso normal do painel para não poluir */
        #vettryx-print-area { display: none; }

        /* Magia do PDF: Quando clica em imprimir, esconde o WP inteiro e mostra só o relatório */
        @media print {
            @page { margin: 0; } /* Remove data e URL nativas do navegador */
            body { margin: 1.6cm; background: #fff !important; }
            
            #wpadminbar, #adminmenuback, #adminmenuwrap, #wpfooter, .vettryx-no-print, .update-nag, .notice { display: none !important; }
            #wpcontent, #wpbody-content { margin-left: 0 !important; padding: 0 !important; }
            
            #vettryx-print-area {
                display: block !important;
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                color: #333;
            }
            .v-report-header { border-bottom: 2px solid #00f076; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
            .v-report-title { font-size: 24px; font-weight: bold; margin: 0; color: #111; }
            .v-report-subtitle { font-size: 14px; color: #666; margin: 5px 0 0 0; }
            .v-box { border: 1px solid #eee; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
            .v-box h3 { margin-top: 0; margin-bottom: 10px; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
            .v-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
            .v-label { font-weight: bold; }
            .v-status-ok { color: #46b450; font-weight: bold; }
            .v-status-fail { color: #dc3232; font-weight: bold; }
        }
    </style>

    <div id="vettryx-print-area">
        <div class="v-report-header">
            <div>
                <h1 class="v-report-title">Relatório de Manutenção Web</h1>
                <p class="v-report-subtitle">Referência: <?php echo esc_html($data['month_year']); ?> | Site: <?php echo get_bloginfo('name'); ?></p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin:0; font-size:20px; color:#00f076;">VETTRYX TECH</h2>
                <p style="margin:0; font-size:12px; color:#666;">vettryx.com.br</p>
            </div>
        </div>

        <div class="v-box">
            <h3>Visão Geral do Ambiente</h3>
            <div class="v-row">
                <span class="v-label">Gestão de Atualizações (Core/Plugins):</span>
                <span class="<?php echo $data['updates_done'] === 'Sim' ? 'v-status-ok' : 'v-status-fail'; ?>"><?php echo esc_html($data['updates_done']); ?></span>
            </div>
            <div class="v-row">
                <span class="v-label">Rotina e Integridade de Backups:</span>
                <span class="<?php echo $data['backups_done'] === 'Sim' ? 'v-status-ok' : 'v-status-fail'; ?>"><?php echo esc_html($data['backups_done']); ?></span>
            </div>
            <div class="v-row">
                <span class="v-label">Monitoramento de Uptime e Segurança:</span>
                <span class="<?php echo $data['security_ok'] === 'Sim' ? 'v-status-ok' : 'v-status-fail'; ?>"><?php echo esc_html($data['security_ok']); ?></span>
            </div>
        </div>

        <div class="v-box">
            <h3>Banco de Horas / Suporte (SLA)</h3>
            <div class="v-row" style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
                <span class="v-label">Horas Consumidas no Período:</span>
                <span><strong><?php echo esc_html($data['hours_used']); ?></strong></span>
            </div>
            <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;"><?php echo esc_html($data['tasks_desc']); ?></div>
        </div>

        <div style="margin-top: 50px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
            Este relatório foi gerado automaticamente pelo sistema VETTRYX WP Core integrado ao ambiente de hospedagem.<br>
            Qualquer dúvida, entre em contato via suporte técnico.
        </div>
    </div>
    <?php
}
