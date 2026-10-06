<?php

namespace Tests\Unit;

use App\Models\Memo;
use PHPUnit\Framework\TestCase;

class MemoExcerptTest extends TestCase
{
    public function test_les_balises_html_de_l_editeur_riche_sont_retirees(): void
    {
        $html = 'Commande 1 : longtemps <div>slmgr /ato</div><div><br></div><div>Commande 2 : Période d’essai </div><div>slmgr /rearm</div>';

        $this->assertSame(
            'Commande 1 : longtemps slmgr /ato Commande 2 : Période d’essai slmgr /rearm',
            Memo::excerptFrom($html)
        );
    }

    public function test_les_entites_et_blocs_ne_collent_pas_les_mots(): void
    {
        $html = '<h2>JSE EXPRESS</h2><p><span>Version&nbsp;: MVP Adzopé</span></p><blockquote><p>Cette &amp; celle-là</p></blockquote><p><strong><code>Zone.php</code></strong> validé.</p>';

        $this->assertSame('JSE EXPRESS Version : MVP Adzopé Cette & celle-là Zone.php validé.', Memo::excerptFrom($html));
    }

    public function test_le_markdown_est_aplati(): void
    {
        $this->assertSame('Titre point un tâche gras', Memo::excerptFrom("# Titre\n- point un\n- [ ] tâche\n**gras**"));
        $this->assertSame('Titre point un', Memo::excerptFrom("## Titre\n\n1. point un"));
    }

    public function test_l_extrait_est_limite(): void
    {
        $excerpt = Memo::excerptFrom('<p>' . str_repeat('mot ', 100) . '</p>');

        $this->assertLessThanOrEqual(143, mb_strlen($excerpt));
        $this->assertStringEndsWith('...', $excerpt);
    }
}
