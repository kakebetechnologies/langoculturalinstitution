// Draws the website's article HTML (see lib/html.js) with native text: headings, paragraphs, lists, quotes, bold/italic, links, images.
import { useMemo } from 'react';
import { Linking, StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { COLORS, FONTS, RADIUS } from '../constants/theme';
import { mediaUrl } from '../lib/api';
import { htmlToBlocks } from '../lib/html';
import AppText from './AppText';

function Runs({ runs, style }) {
  return (
    <AppText style={style}>
      {runs.map((r, i) => (
        <AppText
          key={i}
          style={[style, r.bold && { fontFamily: FONTS.bold, color: COLORS.black }, r.italic && { fontStyle: 'italic' }, r.href && { color: COLORS.primaryDark, textDecorationLine: 'underline' }]}
          onPress={r.href ? () => Linking.openURL(r.href).catch(() => {}) : undefined}
        >
          {r.text}
        </AppText>
      ))}
    </AppText>
  );
}

export default function RichText({ html, style, compact = false }) {
  const blocks = useMemo(() => htmlToBlocks(html), [html]);

  return (
    <View style={style}>
      {blocks.map((b, i) => {
        if (b.type === 'hr') return <View key={i} style={styles.hr} />;
        if (b.type === 'img') return b.src ? <Image key={i} source={{ uri: mediaUrl(b.src) }} style={compact ? styles.avatar : styles.img} contentFit="cover" accessibilityLabel={b.alt} /> : null;
        if (b.type === 'h2') return <Runs key={i} runs={b.runs} style={[styles.p, styles.h2]} />;
        if (b.type === 'h3' || b.type === 'h4') return <Runs key={i} runs={b.runs} style={[styles.p, styles.h3]} />;
        if (b.type === 'quote') return (
          <View key={i} style={styles.quote}>
            <Runs runs={b.runs} style={[styles.p, styles.quoteText]} />
          </View>
        );
        if (b.type === 'li') return (
          <View key={i} style={styles.li}>
            <AppText style={styles.bullet}>{b.ordered ? `${b.n}.` : '•'}</AppText>
            <Runs runs={b.runs} style={[styles.p, { flex: 1, marginBottom: 6 }]} />
          </View>
        );
        return <Runs key={i} runs={b.runs} style={styles.p} />;
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  p: { fontFamily: FONTS.regular, fontSize: 15.5, lineHeight: 25, color: COLORS.gray900, marginBottom: 12 },
  h2: { fontFamily: FONTS.serif, fontSize: 21, lineHeight: 27, color: COLORS.black, marginTop: 10 },
  h3: { fontFamily: FONTS.serifSemi, fontSize: 17.5, lineHeight: 24, color: COLORS.black, marginTop: 8, marginBottom: 6 },
  li: { flexDirection: 'row', gap: 10, paddingLeft: 4 },
  bullet: { width: 18, fontFamily: FONTS.semibold, color: COLORS.primaryDark, lineHeight: 25 },
  quote: { borderLeftWidth: 4, borderLeftColor: COLORS.primary, backgroundColor: COLORS.paper, paddingHorizontal: 14, paddingTop: 12, marginVertical: 8, borderRadius: RADIUS.md },
  quoteText: { fontFamily: FONTS.serifSemi, fontSize: 16.5, color: COLORS.black },
  hr: { height: 1, backgroundColor: COLORS.line, marginVertical: 12 },
  img: { width: '100%', height: 200, borderRadius: RADIUS.lg, marginVertical: 8 },
  avatar: { width: 72, height: 72, borderRadius: 36, marginBottom: 8, borderWidth: 2, borderColor: COLORS.yellow },
});
