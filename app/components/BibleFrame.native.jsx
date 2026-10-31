// One chapter of the Lango Bible shown inside the app (a WebView on phones). It opens Bible.com's own reader at the chapter, so nobody lands on the page that
// offers to download the app; links that leave Bible.com open in the phone's browser instead of replacing the reader, and the "Get the app" link is hidden.
import { useRef, useState } from 'react';
import { ActivityIndicator, Linking, StyleSheet, View } from 'react-native';
import { WebView } from 'react-native-webview';
import { COLORS } from '../constants/theme';

const INSIDE = /^https:\/\/([a-z0-9-]+\.)?(bible\.com|youversion\.com|youversionapi\.com)(\/|$|\?)/i;
const HIDE_APP_LINKS = `(function(){var s=document.createElement('style');s.textContent='a[href="/app"],a[href^="/app?"],a[href*="itunes.apple.com"],a[href*="play.google.com"]{display:none!important}';(document.head||document.documentElement).appendChild(s);})();true;`;

export default function BibleFrame({ url, style }) {
  const [loading, setLoading] = useState(true);
  const ref = useRef(null);

  function allow(req) {
    const u = req.url || '';
    if (u === 'about:blank' || u.startsWith('data:') || u.startsWith('blob:')) return true;
    if (/^https:\/\/(www\.)?bible\.com\/(?:[a-z]{2,3}\/)?app(\/|\?|$)/i.test(u)) return false; // the page that asks to download the app
    if (INSIDE.test(u)) return true;
    Linking.openURL(u).catch(() => {});
    return false;
  }

  return (
    <View style={[styles.box, style]}>
      <WebView
        ref={ref}
        key={url}
        source={{ uri: url }}
        originWhitelist={['https://*']}
        onShouldStartLoadWithRequest={allow}
        injectedJavaScriptBeforeContentLoaded={HIDE_APP_LINKS}
        injectedJavaScript={HIDE_APP_LINKS}
        onLoadEnd={() => setLoading(false)}
        setSupportMultipleWindows={false}
        javaScriptEnabled
        domStorageEnabled
        allowsBackForwardNavigationGestures
        style={{ flex: 1, backgroundColor: COLORS.white }}
      />
      {loading ? (
        <View style={styles.loading} pointerEvents="none">
          <ActivityIndicator color={COLORS.primary} />
        </View>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  box: { flex: 1, backgroundColor: COLORS.white },
  loading: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center', backgroundColor: 'rgba(255,255,255,0.7)' },
});
