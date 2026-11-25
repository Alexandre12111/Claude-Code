<?php
/**
 * Générateur PDF minimal, sans dépendance : titres, paragraphes, tableaux, sauts de page.
 * Police Helvetica (encodage Windows-1252 : accents et symbole €).
 */
defined( 'ABSPATH' ) || exit;

class AC_Pdf {

	const W = 595.28, H = 841.89, M = 42;

	private $pages = array();
	private $cur = '';
	private $y = 0;
	private $title;

	/** Largeurs Helvetica (1/1000 em) des caractères 32 à 126. */
	const WIDTHS = array( 278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278, 556, 556, 556, 556, 556, 556, 556, 556, 556, 556,
		278, 278, 584, 584, 584, 556, 1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778, 667, 778, 722, 667, 611, 722, 667,
		944, 667, 667, 611, 278, 278, 278, 469, 556, 333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556, 556, 556, 333, 500,
		278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584 );

	public function __construct( string $title ) {
		$this->title = $title;
		$this->new_page();
	}

	private static function enc( string $s ): string {
		$s = str_replace( array( "\u{202f}", "\u{00a0}", '’', '…', '≠', '−', '→', '≤', '≥', '✓' ), array( ' ', ' ', "'", '...', '<>', '-', '->', '<=', '>=', 'v' ), $s );
		$s = function_exists( 'iconv' ) ? (string) @iconv( 'UTF-8', 'Windows-1252//TRANSLIT', $s ) : mb_convert_encoding( $s, 'Windows-1252', 'UTF-8' );
		return str_replace( array( '\\', '(', ')', "\r", "\n" ), array( '\\\\', '\\(', '\\)', ' ', ' ' ), $s );
	}

	public static function width( string $s, float $size, bool $bold = false ): float {
		$w   = 0;
		$raw = function_exists( 'iconv' ) ? (string) @iconv( 'UTF-8', 'Windows-1252//TRANSLIT', str_replace( "\u{202f}", ' ', $s ) ) : $s;
		for ( $i = 0, $n = strlen( $raw ); $i < $n; $i++ ) {
			$c  = ord( $raw[ $i ] );
			$w += ( $c >= 32 && $c <= 126 ) ? self::WIDTHS[ $c - 32 ] : 556;
		}
		return $w * $size / 1000 * ( $bold ? 1.06 : 1 );
	}

	private function new_page(): void {
		if ( '' !== $this->cur ) {
			$this->pages[] = $this->cur;
		}
		$this->cur = '';
		$this->y   = self::H - self::M;
	}

	private function need( float $h ): void {
		if ( $this->y - $h < self::M + 20 ) {
			$this->new_page();
		}
	}

	public function text( float $x, float $y, string $s, float $size = 9, bool $bold = false, string $color = '0 0 0' ): void {
		$this->cur .= sprintf( "BT %s rg /%s %.1f Tf %.2f %.2f Td (%s) Tj ET\n", $color, $bold ? 'F2' : 'F1', $size, $x, $y, self::enc( $s ) );
	}

	private function line( float $x1, float $y1, float $x2, float $y2, string $gray = '0.85' ): void {
		$this->cur .= sprintf( "%s G 0.5 w %.2f %.2f m %.2f %.2f l S\n", $gray, $x1, $y1, $x2, $y2 );
	}

	private function wrap( string $s, float $maxw, float $size, bool $bold = false ): array {
		$out  = array();
		$line = '';
		foreach ( preg_split( '/\s+/u', trim( $s ) ) as $word ) {
			$try = '' === $line ? $word : "$line $word";
			if ( self::width( $try, $size, $bold ) > $maxw && '' !== $line ) {
				$out[] = $line;
				$line  = $word;
			} else {
				$line = $try;
			}
		}
		$out[] = $line;
		return $out;
	}

	public function h1( string $s ): void {
		$this->need( 30 );
		$this->text( self::M, $this->y - 16, $s, 16, true, '0.12 0.14 0.19' );
		$this->y -= 26;
	}

	public function h2( string $s ): void {
		$this->need( 26 );
		$this->y -= 8;
		$this->text( self::M, $this->y - 12, $s, 11.5, true, '0.12 0.14 0.19' );
		$this->y -= 18;
	}

	public function p( string $s, float $size = 8.5, string $color = '0 0 0' ): void {
		foreach ( $this->wrap( $s, self::W - 2 * self::M, $size ) as $l ) {
			$this->need( $size + 4 );
			$this->text( self::M, $this->y - $size, $l, $size, false, $color );
			$this->y -= $size + 3.5;
		}
		$this->y -= 3;
	}

	/** @param array $rows première ligne = en tête ; $num = index des colonnes alignées à droite */
	public function table( array $rows, ?array $widths = null, array $num = array() ): void {
		if ( ! $rows ) {
			return;
		}
		$n     = count( $rows[0] );
		$total = self::W - 2 * self::M;
		$widths = $widths ? array_map( function ( $w ) use ( $total, $widths ) { return $w / array_sum( $widths ) * $total; }, $widths ) : array_fill( 0, $n, $total / $n );
		$size  = 7.8;
		foreach ( $rows as $i => $row ) {
			$cells = array();
			$h     = 0;
			foreach ( array_values( $row ) as $j => $c ) {
				$cells[ $j ] = $this->wrap( (string) $c, $widths[ $j ] - 6, $size, 0 === $i );
				$h           = max( $h, count( $cells[ $j ] ) );
			}
			$rh = $h * ( $size + 2.5 ) + 5;
			$this->need( $rh );
			if ( 0 === $i ) {
				$this->cur .= sprintf( "0.93 0.94 0.96 rg %.2f %.2f %.2f %.2f re f\n", self::M, $this->y - $rh, $total, $rh );
			}
			$x = self::M;
			foreach ( $cells as $j => $lines ) {
				foreach ( $lines as $k => $l ) {
					$tx = in_array( $j, $num, true ) ? $x + $widths[ $j ] - 3 - self::width( $l, $size, 0 === $i ) : $x + 3;
					$this->text( $tx, $this->y - 3 - $size - $k * ( $size + 2.5 ), $l, $size, 0 === $i );
				}
				$x += $widths[ $j ];
			}
			$this->y -= $rh;
			$this->line( self::M, $this->y, self::M + $total, $this->y );
		}
		$this->y -= 6;
	}

	public function output(): string {
		$this->new_page();
		$company = AC_DB::get_setting( 'company' );
		$foot    = $company['name'] . ' ' . $company['legal_form'] . ' · SIREN ' . ( $company['siren'] ?: 'à renseigner' ) . ' · ' . $this->title . ' · édité le ' . current_time( 'd/m/Y' );
		$objs    = array( 1 => '', 2 => '', 3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
			4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>' );
		$kids    = array();
		$np      = count( $this->pages );
		foreach ( $this->pages as $i => $content ) {
			$content .= sprintf( "BT 0.45 0.45 0.45 rg /F1 7 Tf %.2f %.2f Td (%s) Tj ET\n", self::M, 24, self::enc( $foot . ' · page ' . ( $i + 1 ) . "/$np" ) );
			$cid      = count( $objs ) + 1;
			$objs[ $cid ] = '<< /Length ' . strlen( $content ) . " >>\nstream\n" . $content . "endstream";
			$pid      = count( $objs ) + 1;
			$objs[ $pid ] = sprintf( '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>', self::W, self::H, $cid );
			$kids[]   = "$pid 0 R";
		}
		$objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
		$objs[2] = '<< /Type /Pages /Kids [' . implode( ' ', $kids ) . '] /Count ' . count( $kids ) . ' >>';
		$info    = count( $objs ) + 1;
		$objs[ $info ] = '<< /Title (' . self::enc( $this->title ) . ') /Producer (Aurea Compta) >>';
		$out     = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
		$offsets = array();
		foreach ( $objs as $id => $o ) {
			$offsets[ $id ] = strlen( $out );
			$out           .= "$id 0 obj\n$o\nendobj\n";
		}
		$xref = strlen( $out );
		$out .= 'xref' . "\n0 " . ( count( $objs ) + 1 ) . "\n0000000000 65535 f \n";
		foreach ( $offsets as $off ) {
			$out .= sprintf( "%010d 00000 n \n", $off );
		}
		$out .= 'trailer << /Size ' . ( count( $objs ) + 1 ) . " /Root 1 0 R /Info $info 0 R >>\nstartxref\n$xref\n%%EOF\n";
		return $out;
	}

	/** Raccourci : blocs ['h', texte] | ['p', texte] | ['table', lignes, largeurs, colonnes numériques] */
	public static function build( string $title, array $blocks ): string {
		$pdf     = new self( $title );
		$company = AC_DB::get_setting( 'company' );
		$pdf->h1( $title );
		$pdf->p( $company['name'] . ' ' . $company['legal_form'] . ' · SIREN ' . ( $company['siren'] ?: 'à renseigner' ) . ' · édité le ' . current_time( 'd/m/Y' ), 7.5, '0.4 0.4 0.4' );
		foreach ( $blocks as $b ) {
			if ( 'h' === $b[0] ) {
				$pdf->h2( $b[1] );
			} elseif ( 'p' === $b[0] ) {
				$pdf->p( $b[1] );
			} else {
				$pdf->table( $b[1], $b[2] ?? null, $b[3] ?? array() );
			}
		}
		return $pdf->output();
	}
}
