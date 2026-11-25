<?php
/**
 * Écriture de classeurs Excel (.xlsx) sans dépendance, et ZIP.
 */
defined( 'ABSPATH' ) || exit;

class AC_Xlsx {

	private static function col( int $i ): string {
		$s = '';
		for ( $i++; $i > 0; $i = intdiv( $i - 1, 26 ) ) {
			$s = chr( 65 + ( $i - 1 ) % 26 ) . $s;
		}
		return $s;
	}

	private static function esc( $v ): string {
		return htmlspecialchars( (string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
	}

	/** @param array $sheets nom => lignes (la première ligne est l'en tête) */
	public static function build( array $sheets ): string {
		$files = array();
		$i     = 0;
		$ws    = '';
		$rels  = '';
		$ct    = '';
		foreach ( $sheets as $name => $rows ) {
			$i++;
			$xml   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
			$width = array();
			foreach ( array_values( $rows ) as $r => $row ) {
				$xml .= '<row r="' . ( $r + 1 ) . '">';
				foreach ( array_values( $row ) as $c => $v ) {
					$ref         = self::col( $c ) . ( $r + 1 );
					$style       = 0 === $r ? ' s="1"' : '';
					$width[ $c ] = max( $width[ $c ] ?? 8, min( 60, mb_strlen( (string) $v ) + 2 ) );
					if ( ( is_int( $v ) || is_float( $v ) ) && 0 !== $r ) {
						$xml .= "<c r=\"$ref\"$style><v>$v</v></c>";
					} elseif ( null !== $v && '' !== $v ) {
						$xml .= "<c r=\"$ref\" t=\"inlineStr\"$style><is><t xml:space=\"preserve\">" . self::esc( $v ) . '</t></is></c>';
					}
				}
				$xml .= '</row>';
			}
			$cols = '';
			foreach ( $width as $c => $w ) {
				$cols .= '<col min="' . ( $c + 1 ) . '" max="' . ( $c + 1 ) . "\" width=\"$w\" customWidth=\"1\"/>";
			}
			$xml = str_replace( '<sheetData>', ( $cols ? "<cols>$cols</cols>" : '' ) . '<sheetData>', $xml ) . '</sheetData></worksheet>';
			$files[ "xl/worksheets/sheet$i.xml" ] = $xml;
			$ws   .= '<sheet name="' . self::esc( mb_substr( $name, 0, 31 ) ) . "\" sheetId=\"$i\" r:id=\"rId$i\"/>";
			$rels .= "<Relationship Id=\"rId$i\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet$i.xml\"/>";
			$ct   .= "<Override PartName=\"/xl/worksheets/sheet$i.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
		}
		$n = $i + 1;
		$files['[Content_Types].xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . $ct . '</Types>';
		$files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
		$files['xl/workbook.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
			. 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' . $ws . '</sheets></workbook>';
		$files['xl/_rels/workbook.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. $rels . "<Relationship Id=\"rId$n\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/></Relationships>";
		$files['xl/styles.xml'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
			. '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
			. '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F2430"/></patternFill></fill></fills>'
			. '<borders count="1"><border/></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
			. '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
			. '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
		return self::zip( $files );
	}

	/** @param array $files chemin => contenu ; chemin => ['file' => chemin disque] pour un fichier existant */
	public static function zip( array $files ): string {
		if ( ! class_exists( 'ZipArchive' ) ) {
			throw new AC_Error( "L'extension PHP « zip » est nécessaire pour les exports (à activer chez l'hébergeur)." );
		}
		$tmp = tempnam( get_temp_dir(), 'aurea-compta' );
		$z   = new ZipArchive();
		$z->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		foreach ( $files as $name => $content ) {
			if ( is_array( $content ) ) {
				if ( file_exists( $content['file'] ) ) {
					$z->addFile( $content['file'], $name );
				}
			} else {
				$z->addFromString( $name, $content );
			}
		}
		$z->close();
		$data = file_get_contents( $tmp );
		@unlink( $tmp );
		return $data;
	}
}
