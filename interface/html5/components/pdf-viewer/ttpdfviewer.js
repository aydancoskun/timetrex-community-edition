/*
 * For future reference, quite a lot of this code is based on the following, and potentially more, but these are the links I remember:
 * https://github.com/mozilla/pdf.js/tree/master/examples/components
 * https://github.com/mozilla/pdf.js/tree/master/examples/acroforms
 * https://github.com/mozilla/pdfjs-dist/tree/master/web
 * https://github.com/mozilla/pdf.js/tree/master/web
 *
 * JSFiddle examples: https://jsfiddle.net/redfox05/btaqyse6/ , https://jsfiddle.net/redfox05/xvpzwLc2/ , https://jsfiddle.net/redfox05/rdyzef3o/
 */

import * as pdfjsLib from 'pdfjs-dist';
import * as pdfjsViewer from 'pdfjs-dist/web/pdf_viewer';
import 'pdfjs-dist/web/pdf_viewer.css';

// window.pdfjsLib = pdfjsLib;
// window.pdfjsViewer = pdfjsViewer;

export class TTPDFViewer {
	constructor( options ) {
		this.pdf_count = 0;
		this.url_array = [];
		this.target = null;

		return this.initPDFViewer( options.urls, options.target );
	}

	initPDFViewer( url_array, target ) {
		this.url_array = url_array; // needed as a global for the eventBus to trigger the next document load.
		this.target = target; // Also needed for the eventBus next doc loading.

		if( !Array.isArray( url_array )) {
			url_array = [ url_array ];
		}

		if( url_array.length > 1 ) {
			$('.resume-label .label' ).text( $.i18n._( 'Resume' ) + ' (' + url_array.length + ' ' + $.i18n._( 'documents' ) + ')');
		}
		this.loadMultiplePDF( url_array, target );
	}

	loadMultiplePDF( url_array, target ) {
		// Load first PDF via initPDFViewer, and loads the rest in callbacks.
		if( Array.isArray( url_array ) && url_array.length > 0 ) {
			var current_url = url_array.pop();

			// If not the first page, then add a divider between multiple PDF documents.
			if( this.pdf_count !== 0 ) {
				var document_divider = document.createElement( 'hr' );
				target.appendChild( document_divider );
			}

			// Start to cycle through each pdf, loading the first one
			var container = document.createElement( 'div' );
			container.className = 'pdfContainer';
			container.setAttribute('data-document-index', this.pdf_count);
			target.appendChild( container );

			this.loadPDF( current_url, container, this.pdf_count );

			this.pdf_count++;
		}
	}

	loadPDF( pdf_url, container, pdf_id ) {
		if (!pdfjsLib.getDocument || !pdfjsViewer.PDFPageView) {
			alert("Please build the pdfjs-dist library using\n  `gulp dist-install`");
		}

		// The workerSrc property shall be specified.
		// pdfjsLib.GlobalWorkerOptions.workerSrc = "../../node_modules/pdfjs-dist/build/pdf.worker.js";
		pdfjsLib.GlobalWorkerOptions.workerSrc = "dist/pdf.worker.js";

		// Some PDFs need external cmaps.
		// var CMAP_URL = "../../node_modules/pdfjs-dist/cmaps/";
		var CMAP_URL = "dist/pdfjs-cmaps/"; // This cmap directory is automatically copied by webpack config from from node_modules into dist at build.
		var CMAP_PACKED = true;

		// var DEFAULT_URL = "../../web/compressed.tracemonkey-pldi-09.pdf";
		var DEFAULT_URL = pdf_url;
		var DEFAULT_SCALE = 1.0;
		var CSS_UNITS = 96/72; // Fixed scale calculations by taking into account CSS units. https://github.com/mozilla/pdf.js/issues/5628#issuecomment-367399215

		// Already passed into the function.
		// var container = document.getElementById("pageContainer");

		var eventBus = new pdfjsViewer.EventBus();

		// AlternativeLoadNextPDFDocument - In case the current logic does not work out. See code at bottom of loadingTask.
		// eventBus.on("pagesloaded", () => {
		// 	console.log('pagesloaded');
		// 	this.loadMultiplePDF( this.url_array, this.target );
		// });

		// Loading document.
		var loadingTask = pdfjsLib.getDocument({
			url: DEFAULT_URL,
			cMapUrl: CMAP_URL,
			cMapPacked: CMAP_PACKED,
		});

		// Most of this logic is taken from the pdfjs acroforms example at https://github.com/mozilla/pdf.js/blob/master/examples/acroforms/acroforms.js

		loadingTask.promise.then((pdfDocument) => {
			// Use a promise to fetch and render the next page.
			var promise = Promise.resolve();

			for (var i = 1; i <= pdfDocument.numPages; i++) {
				promise = promise.then(function( pageNum ) {

					// Document loaded, retrieving the page.
					return pdfDocument.getPage( pageNum ).then(function (pdfPage) {

						var available_space = container.clientWidth;
						var desired_width = available_space - 10 - 5 - 20;// 10px: margin on row, 5px: margin-right on column. The extra 20px was trial and error, likely the scrollbar, which will vary on browser.
						var viewport = pdfPage.getViewport({ scale: DEFAULT_SCALE });
						var scaled_scale = desired_width / (viewport.width * CSS_UNITS); // Fixed scale calculations by taking into account CSS units. https://github.com/mozilla/pdf.js/issues/5628#issuecomment-367399215
						// var scaled_viewport = pdfPage.getViewport({ scale: scaled_scale });


						// Creating the page view with default parameters.
						var pdfPageView = new pdfjsViewer.PDFPageView({
							container: container,
							id: pageNum,
							// scale: SCALE,
							scale: scaled_scale,
							defaultViewport: pdfPage.getViewport({ scale: scaled_scale }),
							eventBus: eventBus,
							// We can enable text/annotations layers, if needed
							textLayerFactory: new pdfjsViewer.DefaultTextLayerFactory(),
							annotationLayerFactory: new pdfjsViewer.DefaultAnnotationLayerFactory(),
							renderInteractiveForms: true, // inspired from the acroforms example at https://github.com/mozilla/pdf.js/tree/master/examples/acroforms - a quick one-line win, so might as well enable it.
						});
						// Associates the actual page with the view, and drawing it
						pdfPageView.setPdfPage(pdfPage);
						return pdfPageView.draw().then(() => {
							$('.pdfContainer a').attr('target', '_blank'); // #2662 and #2819 Needed to open PDF links in a new window, rather than active. PDFJS documentation is lacking on how to do this properly. So this will change the link target to _blank for all the PDF docs on the page. For future attemps, I looked into LinkService, externalLinkTarget, and newWindow, but examples did not work for our solution.
						});
					});
				}.bind(null, i) );
			}
			// Either load the next file here, or use the eventBus code, which is commented out further up. See AlternativeLoadNextPDFDocument
			this.loadMultiplePDF( this.url_array, this.target );
		});

	}
}
