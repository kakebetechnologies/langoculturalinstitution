// Labelled text input with an inline error message, an optional hint, and a show/hide toggle for passwords.
import { useState } from 'react';
import { Platform, Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SPACING } from '../constants/theme';
import AppText from './AppText';

export default function TextField({ label, error, hint, secureTextEntry, multiline, style, inputStyle, ...inputProps }) {
  const [hidden, setHidden] = useState(!!secureTextEntry);
  const [focused, setFocused] = useState(false);

  return (
    <View style={[styles.wrap, style]}>
      {label ? (
        <AppText variant="label" style={styles.label}>
          {label}
        </AppText>
      ) : null}
      <View style={[styles.field, multiline && styles.multi, focused && styles.focused, !!error && styles.invalid]}>
        <TextInput
          maxFontSizeMultiplier={MAX_FONT_SCALE}
          {...inputProps}
          multiline={multiline}
          secureTextEntry={hidden}
          onFocus={(e) => {
            setFocused(true);
            inputProps.onFocus?.(e);
          }}
          onBlur={(e) => {
            setFocused(false);
            inputProps.onBlur?.(e);
          }}
          placeholderTextColor={COLORS.gray500}
          style={[styles.input, multiline && styles.inputMulti, inputStyle]}
        />
        {secureTextEntry ? (
          <Pressable accessibilityLabel={hidden ? 'Show password' : 'Hide password'} onPress={() => setHidden((h) => !h)} hitSlop={10}>
            <Ionicons name={hidden ? 'eye-outline' : 'eye-off-outline'} size={20} color={COLORS.gray700} />
          </Pressable>
        ) : null}
      </View>
      {error ? (
        <AppText variant="caption" style={styles.error}>
          {error}
        </AppText>
      ) : hint ? (
        <AppText variant="caption" style={styles.hint}>
          {hint}
        </AppText>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { marginBottom: SPACING.md },
  label: { marginBottom: 6 },
  field: { flexDirection: 'row', alignItems: 'center', minHeight: 50, paddingHorizontal: SPACING.md, borderRadius: RADIUS.md, borderWidth: 1.5, borderColor: COLORS.gray300, backgroundColor: COLORS.white },
  multi: { alignItems: 'flex-start' },
  focused: { borderColor: COLORS.sky },
  invalid: { borderColor: COLORS.danger },
  input: { flex: 1, fontFamily: FONTS.regular, fontSize: 15, color: COLORS.black, paddingVertical: 10, ...Platform.select({ web: { outlineStyle: 'none' } }) },
  inputMulti: { minHeight: 110, textAlignVertical: 'top' },
  error: { color: COLORS.danger, marginTop: 4 },
  hint: { marginTop: 4 },
});
