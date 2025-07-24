<template>
  <button @click="sendTip" :disabled="loading">
    <slot>{{ wp.i18n.__('Tip', 'artpulse') }}</slot>
  </button>
</template>

<script>
export default {
  props: {
    artistId: { type: Number, required: true },
    amount: { type: Number, default: 1 }
  },
  data() {
    return { loading: false };
  },
  methods: {
    async sendTip() {
      this.loading = true;
      try {
        await fetch(`/wp-json/artpulse/v1/artist/${this.artistId}/tip`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ amount: this.amount })
        });
        this.$emit('tipped');
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
