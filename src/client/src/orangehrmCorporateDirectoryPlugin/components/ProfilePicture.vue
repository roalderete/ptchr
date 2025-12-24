<template>
  <div class="orangehrm-profile-picture">
    <img
      :src="imgSrc"
      alt="Profile Picture"
      class="orangehrm-profile-picture-img"
    />
    <span
      class="orangehrm-profile-picture-status"
      :class="isOnline ? 'online' : 'offline'"
      :data-tooltip="isOnline ? 'Online' : 'Offline'"
      :aria-label="isOnline ? 'Online' : 'Offline'"
      role="img"
      tabindex="0"
    />
  </div>
</template>

<script>
import {computed} from 'vue';

const defaultPic = `${window.appGlobal.publicPath}/images/default-photo.png`;

export default {
  name: 'ProfilePicture',
  props: {
    id: {
      type: Number,
      required: true,
    },
    isOnline: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    const imgSrc = computed(() => {
      return props.id
        ? `${window.appGlobal.baseUrl}/pim/viewPhoto/empNumber/${props.id}`
        : defaultPic;
    });

    return {
      imgSrc,
    };
  },
};
</script>

<style lang="scss" scoped>
.orangehrm-profile-picture {
  display: block;
  position: relative;
  height: 90px;
  width: 90px;
  margin: 0 auto;
  /* allow the status dot to extend outside the circular photo */
  overflow: visible;
  border-radius: 50%;
  & img {
    height: 90px;
    width: 90px;
    display: block;
    border-radius: 50%;
    object-fit: cover;
  }

  &-status {
    position: absolute;
    /* move slightly outside the right-bottom edge so it's fully visible */
    right: -6px;
    bottom: -6px;
    height: 12px;
    width: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
    z-index: 5;
    /* allow hover / focus so tooltips work */
    pointer-events: auto;

    /* small tooltip shown on hover or keyboard focus */
    &::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: 140%;
      right: 50%;
      transform: translateX(50%) translateY(0);
      white-space: nowrap;
      background-color: rgba(0, 0, 0, 0.75);
      color: #fff;
      font-size: 11px;
      padding: 4px 6px;
      border-radius: 3px;
      opacity: 0;
      transition: opacity 0.12s ease, transform 0.12s ease;
      pointer-events: none;
      z-index: 10;
    }

    &:hover::after,
    &:focus::after {
      opacity: 1;
      transform: translateX(50%) translateY(-6px);
    }
  }

  &-status.online {
    background-color: #28a745; /* green */
  }
  &-status.offline {
    background-color: #bdbdbd; /* gray */
  }
}
</style>
