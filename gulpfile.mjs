import { dest, series, src } from 'gulp'
import concat from 'gulp-concat'
import { deleteSync } from 'del'
import rev from 'gulp-rev'

export function clean (cb) {
  deleteSync('public/css/*.css')
  deleteSync('public/css/*.png')
  deleteSync('public/js/*.js')
  deleteSync('public/js/translations')
  deleteSync('public/manifest.json')
  cb()
}

export function buildCss (cb) {
  src([
    'assets/css/bootstrap.css',
    'assets/css/style.css',
    'assets/css/icons.css',
    'assets/css/languages.css'
  ], { base: 'assets' })
    .pipe(concat({ path: 'css/styles.css' }))
    .pipe(rev())
    .pipe(dest('public'))
    .pipe(rev.manifest('public/manifest.json', { base: 'public' }))
    .pipe(dest('public'))
  cb()
}

export function copyLanguagesImage (cb) {
  src('assets/css/languages.png').pipe(dest('public/css'))
  cb()
}

export function buildVendorJs (cb) {
  src([
    'assets/js/bootstrap.js',
    'assets/js/bootstrap-markdown.de.js',
    'assets/js/bootstrap-markdown.es.js',
    'assets/js/jquery.toc.js'
  ], { base: 'assets' })
    .pipe(dest('public'))
    .pipe(concat({ path: 'js/vendor.js' }))
    .pipe(rev())
    .pipe(dest('public'))
    .pipe(rev.manifest('public/manifest.json', { base: 'public', merge: true }))
    .pipe(dest('public'))
  cb()
}

export function buildAppJs (cb) {
  // aggregate the commonly used JS files into one "app.js" file
  src([
    'assets/js/app.config.js',
    'assets/js/app.data.js',
    'assets/js/app.format.js',
    'assets/js/app.tip.js',
    'assets/js/app.card_modal.js',
    'assets/js/app.user.js',
    'assets/js/app.binomial.js',
    'assets/js/app.hypergeometric.js',
    'assets/js/app.draw_simulator.js',
    'assets/js/app.textcomplete.js',
    'assets/js/app.markdown.js',
    'assets/js/app.smart_filter.js',
    'assets/js/app.deck.js',
    'assets/js/app.diff.js',
    'assets/js/app.deck_history.js',
    'assets/js/app.deck_charts.js',
    'assets/js/app.ui.js'
  ], { base: 'assets' })
    .pipe(dest('public'))
    .pipe(concat({ path: 'js/app.js' }))
    .pipe(rev())
    .pipe(dest('public'))
    .pipe(rev.manifest('public/manifest.json', { base: 'public', merge: true }))
    .pipe(dest('public'))
  cb()
}

export function copyPageSpecificJs (cb) {
  // these are page-specific, just copy them over to the public directory.
  src([
    'assets/js/ui.card.js',
    'assets/js/ui.deckimport.js',
    'assets/js/ui.deckinit.js',
    'assets/js/ui.deckview.js',
    'assets/js/ui.deckedit.js',
    'assets/js/ui.decks.js',
    'assets/js/ui.decklist_edit.js',
    'assets/js/ui.decklist.js',
    'assets/js/ui.decklist_search.js',
    'assets/js/panels.js'
  ], { base: 'assets' })
    .pipe(dest('public'))
    .pipe(rev())
    .pipe(dest('public'))
    .pipe(rev.manifest('public/manifest.json', { base: 'public', merge: true }))
    .pipe(dest('public'))
  cb()
}

export function buildTranslationsJs (cb) {
  // @todo add a check here to see if translations files have been generated. [ST 2020/06/15]
  src([
    'assets/js/translations/config.js',
    'assets/js/translations/**/*.js'
  ], { base: 'assets' })
    .pipe(dest('public'))
    .pipe(concat({ path: 'js/translations.js' }))
    .pipe(rev())
    .pipe(dest('public'))
    .pipe(rev.manifest('public/manifest.json', { base: 'public', merge: true }))
    .pipe(dest('public'))
  cb()
}

const build = series(
  clean,
  series(
    buildCss,
    buildAppJs,
    buildTranslationsJs,
    buildVendorJs,
    copyPageSpecificJs,
    copyLanguagesImage
  )
)
export default build
